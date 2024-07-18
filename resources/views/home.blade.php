<x-app-layout>
    <!-- <script src="{{ URL::asset('js/main.js') }}"></script> -->
    <script>
        // var stateDirectory = <?php //echo json_encode($dir ?? array('id'=>7,'name'=>'anime')) ?>;
        // var stateFolder = <?php //echo json_encode($folder ?? array('id'=>7,'name'=>'ODDTAXI')) ?>;
        // var stateThumbnailDir = "{{ URL::asset('storage/thumbnails/folders') }}/"
    </script>
    @auth
    <script>var user = true;</script>
    @else
    <script>var user = false;</script>
    @endauth
</x-app-layout>