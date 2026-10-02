import { useBreakpoints } from '@vueuse/core';

export const CUSTOM_BREAKPOINTS = {
    xxs: 288,
    xs: 344,
    xms: 360,
    xm: 400,
    xsm: 432,
    sm: 640,
    md: 768,
    lg: 1024,
    xl: 1280,
    '2xl': 1536,
    '3xl': 2000,
};

export function useReactiveBreakpoints(breakpointsMap: Record<string, number> = CUSTOM_BREAKPOINTS) {
    const breakpoints = useBreakpoints(breakpointsMap);
    return {
        breakpoints,
        isDesktop: breakpoints.greaterOrEqual('lg'),
    };
}
