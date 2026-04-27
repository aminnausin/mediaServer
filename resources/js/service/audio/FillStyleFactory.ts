export type FillStyleFactory = (ctx: CanvasRenderingContext2D, width: number, height: number) => string | CanvasGradient | CanvasPattern;

export const purplePinkGradient: FillStyleFactory = (ctx, _w, h) => {
    const g = ctx.createLinearGradient(0, h, 0, 0);
    g.addColorStop(0, '#9f7aea');
    g.addColorStop(1, '#E6005C');
    return g;
};

export const solidColour =
    (color: string): FillStyleFactory =>
    () =>
        color;

export const verticalGradient =
    (stops: [number, string][]): FillStyleFactory =>
    (ctx, _w, h) => {
        const g = ctx.createLinearGradient(0, h, 0, 0);
        stops.forEach(([offset, colour]) => g.addColorStop(offset, colour));
        return g;
    };

export const horizontalGradient =
    (stops: [number, string][]): FillStyleFactory =>
    (ctx, w, _) => {
        const g = ctx.createLinearGradient(0, 0, w, 0);
        stops.forEach(([offset, colour]) => g.addColorStop(offset, colour));
        return g;
    };
