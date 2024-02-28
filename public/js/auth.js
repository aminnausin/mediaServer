function login(){
    fetch(`/api/login`, {
        method: 'post',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            'email': 'info@tmu.ca',
            'password': '123456789!aB'
        })
    }).then((response) => 
        response.json()
    ).then((json) => {
        console.log(json);
        localStorage.setItem('auth-token', json.data.token)
        toastr['info'](localStorage.getItem('auth-token'));
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
        localStorage.clear('auth-token');
        if(json.success == true) window.location.href = "/testing";
    }).catch((error) => {
        console.log(error);
    });
}