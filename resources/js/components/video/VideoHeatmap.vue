<script setup lang="ts">
import { getScreenSize } from '@/service/util';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { computed } from 'vue';

const props = withDefaults(defineProps<{ playbackData: any[] }>(), { playbackData: () => [] });
const { playbackHeatmap } = storeToRefs(useAppStore());

// Playback Heatmap Graph SVG
const heatMap = computed(() => {
    const start = 'M 0.0,100.0 ';

    function catmullRomFitting(data: string | any[], alpha: number | undefined) {
        if (!data?.length || data.length < 5 || alpha == 0 || alpha === undefined) return '';

        let p0, p1, p2, p3, bp1, bp2, d1, d2, d3, A, B, N, M;
        let d3powA, d2powA, d3pow2A, d2pow2A, d1pow2A, d1powA;
        let d = Math.round(data[0].x) + ',' + Math.round(data[0].y) + ' ';
        const length = data.length;
        for (let i = 0; i < length - 1; i++) {
            p0 = data[Math.max(i - 1, 0)];
            p1 = data[i];
            p2 = data[i + 1];
            p3 = i + 2 < length ? data[i + 2] : p2;

            d1 = Math.sqrt(Math.pow(p0.x - p1.x, 2) + Math.pow(p0.y - p1.y, 2));
            d2 = Math.sqrt(Math.pow(p1.x - p2.x, 2) + Math.pow(p1.y - p2.y, 2));
            d3 = Math.sqrt(Math.pow(p2.x - p3.x, 2) + Math.pow(p2.y - p3.y, 2));

            // Catmull-Rom to Cubic Bezier conversion matrix

            // A = 2d1^2a + 3d1^a * d2^a + d3^2a
            // B = 2d3^2a + 3d3^a * d2^a + d2^2a

            // [   0             1            0          0          ]
            // [   -d2^2a /N     A/N          d1^2a /N   0          ]
            // [   0             d3^2a /M     B/M        -d2^2a /M  ]
            // [   0             0            1          0          ]

            d3powA = Math.pow(d3, alpha);
            d3pow2A = Math.pow(d3, 2 * alpha);
            d2powA = Math.pow(d2, alpha);
            d2pow2A = Math.pow(d2, 2 * alpha);
            d1powA = Math.pow(d1, alpha);
            d1pow2A = Math.pow(d1, 2 * alpha);

            A = 2 * d1pow2A + 3 * d1powA * d2powA + d2pow2A;
            B = 2 * d3pow2A + 3 * d3powA * d2powA + d2pow2A;
            N = 3 * d1powA * (d1powA + d2powA);
            if (N > 0) {
                N = 1 / N;
            }
            M = 3 * d3powA * (d3powA + d2powA);
            if (M > 0) {
                M = 1 / M;
            }

            bp1 = {
                x: (-d2pow2A * p0.x + A * p1.x + d1pow2A * p2.x) * N,
                y: (-d2pow2A * p0.y + A * p1.y + d1pow2A * p2.y) * N,
            };

            bp2 = {
                x: (d3pow2A * p1.x + B * p2.x - d2pow2A * p3.x) * M,
                y: (d3pow2A * p1.y + B * p2.y - d2pow2A * p3.y) * M,
            };

            if (bp1.x == 0 && bp1.y == 0) {
                bp1 = p1;
            }
            if (bp2.x == 0 && bp2.y == 0) {
                bp2 = p2;
            }

            d += 'C' + bp1.x + ',' + bp1.y + ' ' + bp2.x + ',' + bp2.y + ' ' + p2.x + ',' + p2.y + ' ';
        }

        return d;
    }
    return (
        start +
        catmullRomFitting(
            props.playbackData
                ? [
                      ...props.playbackData?.map((entry: { progress: any; count: number }) => {
                          return { x: entry.progress, y: 100 - Math.min(entry.count, 10) * 10 };
                      }),
                      { x: 1000, y: 100 },
                  ]
                : [],
            0.5,
        )
    );
});
</script>
<template>
    <svg
        :class="[
            getScreenSize() === 'default' ? 'opacity-65' : 'opacity-0 peer-hover:opacity-65',
            'ytp-heat-map-svg pointer-events-none h-6 w-full fill-indigo-200/20 transition-opacity duration-200',
        ]"
        preserveAspectRatio="none"
        viewBox="0 0 1000 100"
        v-show="playbackHeatmap"
    >
        <defs>
            <clipPath id="4">
                <path class="ytp-heat-map-path" :d="heatMap"></path>
            </clipPath>
        </defs>
        <rect class="ytp-heat-map-hover" clip-path="url(#4)" fill="white" fill-opacity="0.7" height="100%" width="100%" x="0" y="0"></rect>
    </svg>
</template>
