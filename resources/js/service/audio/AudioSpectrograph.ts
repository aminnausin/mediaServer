const FFT_SIZE = 2048;
const DECIBELS_MIN = -70;
const DECIBELS_MAX = -24;

export class AudioSpectrograph {
    private audioCtx: AudioContext | null = null;
    private audioAnalyser: AnalyserNode | null = null;
    private src: MediaElementAudioSourceNode | null = null;

    private player: HTMLVideoElement;
    private canvas: HTMLCanvasElement;
    private ctx: CanvasRenderingContext2D;

    private dataArray: Uint8Array<ArrayBuffer> | null = null;
    public isDrawing = false;

    private useLog = false;
    private logBins = 32;
    private logRanges: Array<[number, number]> = [];

    constructor(canvas: HTMLCanvasElement, player: HTMLVideoElement) {
        this.player = player;
        this.canvas = canvas;
        this.ctx = canvas.getContext('2d', { willReadFrequently: true })!;
    }

    async attach() {
        if (!this.audioCtx) {
            this.audioCtx = new AudioContext();
            await this.audioCtx.resume();
        }

        this.src = this.audioCtx.createMediaElementSource(this.player);

        this.audioAnalyser = this.audioCtx.createAnalyser();
        this.audioAnalyser.fftSize = FFT_SIZE;
        this.audioAnalyser.minDecibels = DECIBELS_MIN;
        this.audioAnalyser.maxDecibels = DECIBELS_MAX;

        this.src.connect(this.audioAnalyser);
        this.audioAnalyser.connect(this.audioCtx.destination);

        this.dataArray = new Uint8Array(this.audioAnalyser.frequencyBinCount);
    }

    setLogScale(bins: number = 32) {
        // This does not work
        if (!this.audioCtx || !this.audioAnalyser) {
            return;
        }

        // this.useLog = true;
        this.logBins = bins;

        const nyquist = this.audioCtx.sampleRate / 2;
        const binCount = this.audioAnalyser.frequencyBinCount;

        this.logRanges = [];

        let lastFreq = 20; // lowest useful audible freq
        const maxFreq = nyquist;

        for (let i = 0; i < bins; i++) {
            const nextFreq = lastFreq * 2; // 1-octave doubling

            const start = Math.floor((lastFreq / maxFreq) * binCount);
            const end = Math.min(Math.floor((nextFreq / maxFreq) * binCount), binCount - 1);

            this.logRanges.push([start, end]);
            lastFreq = nextFreq;
            if (nextFreq > maxFreq) break;
        }
    }

    setLinearScale() {
        this.useLog = false;
    }

    resize(width: number, height: number) {
        this.canvas.width = width;
        this.canvas.height = height;
    }

    async start() {
        if (this.isDrawing) return;

        if (!this.audioAnalyser || !this.dataArray) {
            await this.attach();
        }

        this.isDrawing = true;
        this.draw();
    }

    stop() {
        this.isDrawing = false;
    }

    private draw() {
        if (!this.isDrawing || !this.audioAnalyser || !this.dataArray) return;

        requestAnimationFrame(() => this.draw());

        // const WIDTH = this.canvas.width;
        // const HEIGHT = this.canvas.height;

        // this.audioAnalyser.getByteFrequencyData(this.dataArray);

        // this.ctx.clearRect(0, 0, WIDTH, HEIGHT);

        // const barWidth = (WIDTH / this.dataArray.length) * 2.5;

        // let x = 0;

        // const gradient = this.ctx.createLinearGradient(0, HEIGHT, 0, 0);
        // gradient.addColorStop(0, '#9f7aea');
        // gradient.addColorStop(1, '#ed64a6');

        // for (let i = 0; i < this.dataArray.length; i++) {
        //     const barHeight = this.dataArray[i] / 2;

        //     this.ctx.fillStyle = gradient;
        //     this.ctx.fillRect(x, HEIGHT - barHeight, barWidth, barHeight);

        //     x += barWidth + 1;
        // }

        const HEIGHT = this.canvas.height;
        const WIDTH = this.canvas.width;

        this.audioAnalyser.getByteFrequencyData(this.dataArray);
        this.ctx.clearRect(0, 0, WIDTH, HEIGHT);
        this.ctx.fillStyle = this.ctx.fillStyle;

        const barWidth = (WIDTH / this.audioAnalyser.frequencyBinCount) * 2.5;

        let barHeight;
        let x = 0;

        if (this.useLog) {
            for (let i = 0; i < this.logRanges.length; i++) {
                const [start, end] = this.logRanges[i];
                if (i == 0) {
                    console.log('draw log');
                }
                // max magnitude in that frequency range
                let max = 0;
                for (let b = start; b <= end; b++) {
                    if (this.dataArray[b] > max) max = this.dataArray[b];
                }

                this.drawBar(x, max, barWidth);
                x += barWidth + 1;
            }
            return;
        }

        for (let i = 0; i < this.audioAnalyser.frequencyBinCount; i++) {
            barHeight = this.dataArray[i];

            this.drawBar(x, barHeight, barWidth);
            x += barWidth + 1;
        }
    }

    private drawBar(x: number, value: number, barWidth: number) {
        const HEIGHT = this.canvas.height;
        const barHeight = value * 0.75;

        // this.ctx.fillStyle = `rgb(${barHeight + 100} 50 50)`;
        this.ctx.fillStyle = `hsl(329.15 ${Math.min(value / HEIGHT / 3 + 70, 100)}% 45%)`;
        this.ctx.fillRect(x, HEIGHT - barHeight, barWidth, barHeight);
        // this.ctx.fillRect(x, HEIGHT - barHeight / 2, barWidth, barHeight / 2);
    }

    destroy() {
        this.stop();
        this.audioCtx?.close();
        this.audioCtx = null;
        this.audioAnalyser = null;
        this.src = null;
    }
}
