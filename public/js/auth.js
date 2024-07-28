document.addEventListener("DOMContentLoaded", function(event) {
    fetch(`/sanctum/csrf-cookie`, {
        method: 'get'
    }).catch((error) => {
        console.log(error);
    });
});

function login(){
    const remember_me = document.getElementById("remember_me");
    fetch(`/api/login`, {
        method: 'post',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            'email': $('#email').val(),
            'password': $('#password').val(), //'123456789!aB'
            'remember_me': remember_me.checked
        })
    }).then((response) => 
        response.json()
    ).then((json) => {
        console.log(json);
        if(json.success){
            localStorage.setItem('auth-token', json.data.token)
            toastr['info'](localStorage.getItem('auth-token'));
            window.location.href = '/';
        }
    }).catch((error) => {
        console.log(error);
    });
}

function logout(){
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(`/logout`, {
        method: 'post',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    }).then(response => 
        response.json()
    ).then(json => {
        console.log(json);
        localStorage.removeItem('auth-token');
        if(json.success == true) window.location.href = "/";
    }).catch((error) => {
        console.log(error);
    });
}