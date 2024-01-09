$(document).ready(function(){
    var pastFirst = false;
    $('.vid-row').off('click').on('click', function(){
        var file = $(this).attr('value');
        var vidSource = document.getElementById('vid-source');
        var folder = $(this).closest('table');
        try {
            vidSource.pause();
        } catch (error) {
            console.log('No video running. Cannot pause.')
        }

        $('#vid-source').attr('src', file);
        vidSource.load();
        if(pastFirst) vidSource.play();
        else pastFirst = true;

        $('#mp4-title').text($(this).text());
        $('#mp4-title-folder').text($(folder).data('folder'));
    })

    $('.vid-row').first().click();

    $('.vid-table').each(function(){
        let id = $(this).attr('id');
        let table = new DataTable('#' + id, {
            //columnDefs: [{ "width": "80%", "targets": 0 }]
            columns: [{ "width": "80%" },{ "width": "20%" }]
        });
        // $('#' + id).DataTable({
        //     responsive: true,
        //     "columns": [{ "width": "80%" },null]
        // })
    })

    toggleDarkMode()

    $('.collapse').removeClass('show');

    $('#dataTable-0-collapse-toggle').trigger('click');

})

$('#dark-mode-toggle').off('click.main').on('click.main', function(){
    toggleDarkMode();
})

$('.folder-toggle').off('click.main').on('click.main', function(){
    var targetID = $(this).data('bs-target');

    if($(targetID).data('state') == 1){
        $(targetID).data('state', 0);
        $(this).empty().append('<i class="bi bi-list"></i> Show Folder');
    } 
    else {
        $(targetID).data('state', 1);
        $(this).empty().append('<i class="bi bi-list"></i> Hide Folder');
    }
});

function toggleDarkMode(){
    let html = document.querySelector('html');
    if (document.getElementById('dark-mode-toggle').checked) {
        $('.dark-mode').addClass('light-mode').removeClass('dark-mode');
        $('.btn-dark').addClass('btn-light').removeClass('btn-dark');
        // html.setAttribute('data-bs-theme', '');
        html.classList.remove("dark");
    }
    else{
        $('.light-mode').addClass('dark-mode').removeClass('light-mode');
        $('.btn-light').addClass('btn-dark').removeClass('btn-light');
        // html.setAttribute('data-bs-theme', 'dark');
        html.classList.add("dark");
    }
}