import type { FillStyleFactory } from './FillStyleFactory';

import { purplePinkGradient } from './FillStyleFactory';

export class AudioSpectrograph {
    private static readonly FFT_SIZE = 2048;
    private static readonly DECIBELS_MIN = -66;
    private static readonly DECIBELS_MAX = -24;
    private static readonly BAR_GAP = 1;

    private static readonly FREQ_MIN = 100;
    private static readonly FREQ_MAX = 16000;
    private static readonly BINS_PER_POINT = 2;
    private static readonly LOG_BINS = 256;
    private static readonly SMOOTHING_FACTOR = 0.95; // 0=instant, 0.98=very smooth, 1=frozen

    private audioCtx: AudioContext | null = null;
    private audioAnalyser: AnalyserNode | null = null;
    private src: MediaElementAudioSourceNode | null = null;

    private readonly player: HTMLVideoElement;
    private readonly canvas: HTMLCanvasElement;
    private readonly ctx: CanvasRenderingContext2D;

    private dataArray: Uint8Array<ArrayBuffer> | null = null;
    private animFrameId: number | null = null;

    public isDrawing = false;

    private dpr = 1;

    private useLog = false;
    private logBins = AudioSpectrograph.LOG_BINS;
    private logRanges: Array<[number, number]> = [];

    private minFreq = AudioSpectrograph.FREQ_MIN;
    private maxFreq = AudioSpectrograph.FREQ_MAX;
    private binsPerPoint = AudioSpectrograph.BINS_PER_POINT;

    private fillStyleFactory: FillStyleFactory = purplePinkGradient;
    private cachedFillStyle: string | CanvasGradient | CanvasPattern | null = null;

    private smoothedData: Float32Array | null = null;
    private readonly smoothingFactor = AudioSpectrograph.SMOOTHING_FACTOR;

    constructor(canvas: HTMLCanvasElement, player: HTMLVideoElement) {
        this.player = player;
        this.canvas = canvas;
        this.ctx = canvas.getContext('2d')!;
        this.dpr = window.devicePixelRatio || 1;
    }

    async attach() {
        if (!this.audioCtx) {
            this.audioCtx = new AudioContext();
            await this.audioCtx.resume();
        }

        if (this.src) return;

        this.src = this.audioCtx.createMediaElementSource(this.player);

        this.audioAnalyser = this.audioCtx.createAnalyser();
        this.audioAnalyser.fftSize = AudioSpectrograph.FFT_SIZE;
        this.audioAnalyser.minDecibels = AudioSpectrograph.DECIBELS_MIN;
        this.audioAnalyser.maxDecibels = AudioSpectrograph.DECIBELS_MAX;

        this.src.connect(this.audioAnalyser);
        this.audioAnalyser.connect(this.audioCtx.destination);

        this.dataArray = new Uint8Array(this.audioAnalyser.frequencyBinCount);
        this.smoothedData = new Float32Array(this.audioAnalyser.frequencyBinCount);
    }

    setLogScale(
        bins: number = AudioSpectrograph.LOG_BINS,
        options?: {
            minFreq?: number;
            maxFreq?: number;
            binsPerPoint?: number;
        },
    ) {
        if (!this.audioCtx || !this.audioAnalyser) return;

        this.logBins = bins;
        this.minFreq = options?.minFreq ?? AudioSpectrograph.FREQ_MIN;
        this.maxFreq = options?.maxFreq ?? AudioSpectrograph.FREQ_MAX;
        this.binsPerPoint = options?.binsPerPoint ?? AudioSpectrograph.BINS_PER_POINT;

        const nyquist = this.audioCtx.sampleRate / 2;
        const binCount = this.audioAnalyser.frequencyBinCount;
        const freqToBin = (f: number) => Math.round((f / nyquist) * binCount);

        this.logRanges = [];

        for (let i = 0; i < bins; i++) {
            // Evenly space bin edges on a log scale between minFreq and nyquist
            const freqStart = this.minFreq * Math.pow(this.maxFreq / this.minFreq, i / bins);
            const freqEnd = this.minFreq * Math.pow(this.maxFreq / this.minFreq, (i + 1) / bins);

            const rawStart = freqToBin(freqStart);
            const rawEnd = freqToBin(freqEnd);

            const clampedEnd = Math.min(Math.max(rawStart + this.binsPerPoint - 1, rawEnd), binCount - 1);

            this.logRanges.push([rawStart, clampedEnd]);
        }

        this.useLog = true;
    }

    setLinearScale() {
        this.useLog = false;
    }

    setFillStyle(factory: FillStyleFactory) {
        this.fillStyleFactory = factory;
        this.cachedFillStyle = null;
    }

    getUseLog() {
        return this.useLog;
    }

    private getFillStyle(): string | CanvasGradient | CanvasPattern {
        if (!this.cachedFillStyle) {
            const w = this.canvas.width / this.dpr;
            const h = this.canvas.height / this.dpr;
            this.cachedFillStyle = this.fillStyleFactory(this.ctx, w, h);
        }
        return this.cachedFillStyle;
    }

    resize(logicalWidth: number, logicalHeight: number) {
        this.dpr = window.devicePixelRatio || 1;

        this.canvas.width = Math.round(logicalWidth * this.dpr);
        this.canvas.height = Math.round(logicalHeight * this.dpr);
        this.canvas.style.width = `${logicalWidth}px`;
        this.canvas.style.height = `${logicalHeight}px`;

        this.ctx.setTransform(this.dpr, 0, 0, this.dpr, 0, 0);
        this.cachedFillStyle = null;
        if (this.useLog) this.setLogScale(this.logBins);
    }

    async start() {
        if (this.isDrawing) {
            return;
        }

        if (!this.audioAnalyser || !this.dataArray) {
            await this.attach();
        }

        this.isDrawing = true;
        this.scheduleFrame();
    }

    stop() {
        this.isDrawing = false;
        if (this.animFrameId !== null) {
            cancelAnimationFrame(this.animFrameId);
            this.animFrameId = null;
        }
    }

    private scheduleFrame() {
        this.animFrameId = requestAnimationFrame(() => this.draw());
    }

    private draw() {
        if (!this.isDrawing || !this.audioAnalyser || !this.dataArray || !this.smoothedData) return;

        const WIDTH = this.canvas.width / this.dpr;
        const HEIGHT = this.canvas.height / this.dpr;
        const BIN_COUNT = this.audioAnalyser.frequencyBinCount;

        this.audioAnalyser.getByteFrequencyData(this.dataArray);
        this.ctx.clearRect(0, 0, WIDTH, HEIGHT);
        this.ctx.fillStyle = this.getFillStyle();

        for (let i = 0; i < this.dataArray.length; i++) {
            this.smoothedData[i] = this.smoothingFactor * this.smoothedData[i] + (1 - this.smoothingFactor) * this.dataArray[i];
        }

        if (this.useLog) {
            this.drawLogFrame(WIDTH, HEIGHT, this.smoothedData);
        } else {
            this.drawLinearFrame(WIDTH, HEIGHT, BIN_COUNT, this.smoothedData);
        }

        this.scheduleFrame();
    }

    private drawLogFrame(WIDTH: number, HEIGHT: number, smoothedData: Float32Array) {
        const barWidth = Math.max(1, (WIDTH - this.logRanges.length * AudioSpectrograph.BAR_GAP) / this.logRanges.length);
        let x = 0;

        for (const [start, end] of this.logRanges) {
            let max = 0;
            for (let b = start; b <= end; b++) {
                if (smoothedData[b] > max) max = smoothedData[b];
            }
            const barHeight = (max / 255) * HEIGHT;
            if (barHeight > 0) this.ctx.fillRect(x, HEIGHT - barHeight, barWidth, barHeight);
            x += barWidth + AudioSpectrograph.BAR_GAP;
        }
    }
    private drawLinearFrame(WIDTH: number, HEIGHT: number, BIN_COUNT: number, smoothedData: Float32Array) {
        const visibleBins = Math.floor(BIN_COUNT / 2);
        const barWidth = Math.max(1, (WIDTH - visibleBins * AudioSpectrograph.BAR_GAP) / visibleBins);
        let x = 0;
        for (let i = 0; i < visibleBins; i++) {
            const barHeight = (smoothedData[i] / 255) * HEIGHT;
            if (barHeight > 0) {
                this.ctx.fillRect(x, HEIGHT - barHeight, barWidth, barHeight);
            }
            x += barWidth + AudioSpectrograph.BAR_GAP;
        }
    }

    destroy() {
        this.stop();
        this.audioCtx?.close();
        this.audioCtx = null;
        this.audioAnalyser = null;
        this.src = null;
        this.dataArray = null;
    }
}
