<x-app-layout>
    <script src="{{ URL::asset('js/auth.js') }}"></script>
    <main class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="neu-card light-mode">
                    <div class="col-sm-12">
                        <div style="display: flex; flex-direction: row-reverse;">
                            <div style="width:auto; display: flex; align-items: center;" class="ms-3">
                                <div class="toggle-switch">
                                    <label class="switch-label">
                                        <input type="checkbox" class="checkbox" id="dark-mode-toggle">
                                        <span class="slider"></span>
                                    </label>
                                </div>
                            </div>
                            <div style="display: flex; justify-content: space-between; width: 100%;">
                                <h3 id="mp4-title"></h3>
                                <h3 id="mp4-title-folder"></h3>
                            </div>
                        </div>
                        <hr class="mt-2">
                        <video id="vid-source" width="100%" src="" type="video/mp4" controls>
                        </video>
                    </div>

                    <hr id='preData'>

                    <div id='dataContainer'></div>
                </div>
            </div>
        </div>
    </main>
</x-app-layout>