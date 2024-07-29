<script setup>
import { storeToRefs } from 'pinia';
import { useContentStore } from '../../stores/ContentStore';
import { computed, ref, watch } from 'vue';

// Heatmap can be composable
const heatMapData = ref([{ x: 25, y: 60 }, { x: 154, y: 48 }, { x: 266, y: 12 }, { x: 585, y: 18 },{ x: 799, y: 16 }, { x: 1000, y: 100 }]);
const heatMap = computed(() => {
    const start = 'M 0.0,100.0 ';

    var catmullRomFitting = function (data, alpha) {

        if (alpha == 0 || alpha === undefined) {
            return false;
        } else {
            var p0, p1, p2, p3, bp1, bp2, d1, d2, d3, A, B, N, M;
            var d3powA, d2powA, d3pow2A, d2pow2A, d1pow2A, d1powA;
            var d = Math.round(data[0].x) + ',' + Math.round(data[0].y) + ' ';
            var length = data.length;
            for (var i = 0; i < length - 1; i++) {

                p0 = i == 0 ? data[0] : data[i - 1];
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
                    y: (-d2pow2A * p0.y + A * p1.y + d1pow2A * p2.y) * N
                };

                bp2 = {
                    x: (d3pow2A * p1.x + B * p2.x - d2pow2A * p3.x) * M,
                    y: (d3pow2A * p1.y + B * p2.y - d2pow2A * p3.y) * M
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
    };
    return start + catmullRomFitting(heatMapData.value, 0.5);
});

const currentID = ref(-1);
const ContentStore = useContentStore();
const { stateVideo } = storeToRefs(ContentStore);
const { createRecord, updateViewCount } = ContentStore;

const initVideoPlayer = () => {
    // let vidSource = document.getElementById('vid-source');
    let root = document.getElementById('root');

    root.scrollIntoView();

    // if (pastFirst.value === true) vidSource.play();
}

const playVideo = () => {
    console.log(stateVideo.value.id);
    if(currentID.value === stateVideo.value.id){ 
        console.log(stateVideo.value.id + ' already seen');
        return; 
    }// stop recording every time video seek
    currentID.value = stateVideo.value.id;
    createRecord(stateVideo.value.id);
    updateViewCount(stateVideo.value.id);
}

watch(stateVideo, initVideoPlayer)
</script>

<template>
    <div class="relative group">
        <video id="vid-source" width="100%" :src="stateVideo?.attributes?.path ? `../${stateVideo?.attributes?.path}` : ''" type="video/mp4" controls
            class="focus:outline-none aspect-video flex" @play="playVideo">
            <track kind="captions">
        </video>
        <section class="absolute bottom-6 w-full hidden px-[3%]"> <!-- group-hover:block -->
            <svg class="ytp-heat-map-svg fill-indigo-200/70" height="100%" preserveAspectRatio="none" version="1.1" viewBox="0 0 1000 100"
                width="100%" style="height: 40px;">
                <defs>
                    <!-- <clipPath id="4">
                        <path class="ytp-heat-map-path"
                            d="M 0.0,100.0 C 1.0,92.1 2.0,65.5 5.0,60.3 C 8.0,55.0 11.0,70.8 15.0,73.8 C 19.0,76.7 21.0,74.1 25.0,74.9 C 29.0,75.7 31.0,77.0 35.0,77.7 C 39.0,78.3 41.0,77.6 45.0,78.1 C 49.0,78.5 51.0,79.3 55.0,80.0 C 59.0,80.6 61.0,81.0 65.0,81.2 C 69.0,81.5 71.0,81.8 75.0,81.2 C 79.0,80.7 81.0,79.2 85.0,78.5 C 89.0,77.7 91.0,77.7 95.0,77.6 C 99.0,77.4 101.0,77.6 105.0,77.9 C 109.0,78.1 111.0,79.9 115.0,78.9 C 119.0,78.0 121.0,74.5 125.0,73.0 C 129.0,71.6 131.0,72.0 135.0,71.7 C 139.0,71.4 141.0,71.6 145.0,71.6 C 149.0,71.5 151.0,71.7 155.0,71.5 C 159.0,71.3 161.0,72.3 165.0,70.6 C 169.0,68.9 171.0,64.8 175.0,63.0 C 179.0,61.1 181.0,62.1 185.0,61.4 C 189.0,60.7 191.0,60.1 195.0,59.6 C 199.0,59.0 201.0,59.2 205.0,58.7 C 209.0,58.2 211.0,56.8 215.0,57.1 C 219.0,57.4 221.0,58.9 225.0,60.1 C 229.0,61.4 231.0,63.1 235.0,63.2 C 239.0,63.3 241.0,61.0 245.0,60.6 C 249.0,60.1 251.0,62.1 255.0,61.2 C 259.0,60.2 261.0,57.0 265.0,55.9 C 269.0,54.8 271.0,55.7 275.0,55.7 C 279.0,55.8 281.0,56.1 285.0,56.2 C 289.0,56.4 291.0,56.4 295.0,56.6 C 299.0,56.9 301.0,58.0 305.0,57.6 C 309.0,57.2 311.0,54.5 315.0,54.5 C 319.0,54.6 321.0,56.5 325.0,57.9 C 329.0,59.3 331.0,60.1 335.0,61.3 C 339.0,62.5 341.0,64.2 345.0,64.0 C 349.0,63.7 351.0,60.6 355.0,60.1 C 359.0,59.7 361.0,61.1 365.0,61.6 C 369.0,62.1 371.0,62.5 375.0,62.8 C 379.0,63.0 381.0,64.9 385.0,63.1 C 389.0,61.2 391.0,56.3 395.0,53.7 C 399.0,51.2 401.0,51.1 405.0,50.2 C 409.0,49.4 411.0,49.9 415.0,49.5 C 419.0,49.1 421.0,48.8 425.0,48.3 C 429.0,47.7 431.0,50.7 435.0,46.9 C 439.0,43.0 441.0,32.9 445.0,29.1 C 449.0,25.3 451.0,28.1 455.0,27.9 C 459.0,27.7 461.0,28.2 465.0,28.1 C 469.0,28.0 471.0,32.1 475.0,27.4 C 479.0,22.8 481.0,10.3 485.0,4.8 C 489.0,-0.7 491.0,0.3 495.0,0.0 C 499.0,-0.3 501.0,-0.0 505.0,3.2 C 509.0,6.3 511.0,13.0 515.0,15.9 C 519.0,18.8 521.0,19.5 525.0,17.7 C 529.0,15.8 531.0,7.7 535.0,6.8 C 539.0,5.8 541.0,10.4 545.0,12.9 C 549.0,15.4 551.0,16.4 555.0,19.3 C 559.0,22.3 561.0,25.3 565.0,27.4 C 569.0,29.6 571.0,28.2 575.0,30.1 C 579.0,32.0 581.0,34.0 585.0,37.0 C 589.0,40.0 591.0,42.1 595.0,45.0 C 599.0,48.0 601.0,49.4 605.0,51.6 C 609.0,53.9 611.0,54.1 615.0,56.2 C 619.0,58.3 621.0,59.8 625.0,62.1 C 629.0,64.4 631.0,65.7 635.0,67.5 C 639.0,69.4 641.0,70.1 645.0,71.4 C 649.0,72.8 651.0,73.3 655.0,74.3 C 659.0,75.3 661.0,75.6 665.0,76.4 C 669.0,77.2 671.0,77.3 675.0,78.4 C 679.0,79.4 681.0,79.7 685.0,81.7 C 689.0,83.7 691.0,86.8 695.0,88.4 C 699.0,90.1 701.0,89.7 705.0,90.0 C 709.0,90.3 711.0,90.0 715.0,90.0 C 719.0,90.0 721.0,90.0 725.0,90.0 C 729.0,90.0 731.0,90.0 735.0,90.0 C 739.0,90.0 741.0,90.0 745.0,90.0 C 749.0,90.0 751.0,90.2 755.0,90.0 C 759.0,89.8 761.0,89.7 765.0,89.2 C 769.0,88.6 771.0,88.3 775.0,87.4 C 779.0,86.6 781.0,85.8 785.0,84.9 C 789.0,83.9 791.0,82.9 795.0,82.7 C 799.0,82.6 801.0,84.2 805.0,84.0 C 809.0,83.9 811.0,83.0 815.0,82.1 C 819.0,81.1 821.0,80.0 825.0,79.3 C 829.0,78.6 831.0,79.0 835.0,78.8 C 839.0,78.6 841.0,78.6 845.0,78.3 C 849.0,78.0 851.0,78.0 855.0,77.3 C 859.0,76.5 861.0,75.2 865.0,74.5 C 869.0,73.8 871.0,74.3 875.0,73.9 C 879.0,73.6 881.0,72.4 885.0,72.7 C 889.0,73.0 891.0,75.2 895.0,75.4 C 899.0,75.6 901.0,74.8 905.0,73.7 C 909.0,72.6 911.0,71.8 915.0,70.1 C 919.0,68.4 921.0,67.0 925.0,65.2 C 929.0,63.4 931.0,61.7 935.0,61.1 C 939.0,60.4 941.0,62.4 945.0,62.1 C 949.0,61.8 951.0,60.8 955.0,59.5 C 959.0,58.2 961.0,56.6 965.0,55.6 C 969.0,54.6 971.0,55.2 975.0,54.5 C 979.0,53.9 981.0,52.4 985.0,52.4 C 989.0,52.4 992.0,53.9 995.0,54.3 C 998.0,54.7 999.0,45.1 1000.0,54.3 C 1001.0,63.4 1000.0,90.9 1000.0,100.0"
                            fill="white"></path>
                    </clipPath> -->
                    <clipPath id="4">
                        <path class="ytp-heat-map-path" :d="heatMap"></path>
                    </clipPath>
                </defs>
                <rect class="ytp-heat-map-graph" clip-path="url(#4)" height="100%"
                    width="100%" x="0" y="0"></rect>
                <!-- <rect class="ytp-heat-map-hover" clip-path="url(#4)" fill="white" fill-opacity="0.7" height="100%"
                    width="100%" x="0" y="0"></rect>
                <rect class="ytp-heat-map-play" clip-path="url(#4)" height="100%" x="0" y="0"></rect> -->
            </svg>
        </section>
    </div>
</template>