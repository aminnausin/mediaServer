 

export const login = async (credentials) => {
    // const remember_me = document.getElementById("remember_me");
    return new Promise(async (resolve, reject) => {
        try {
            const response = await fetch(`/api/login`, {
                method: 'post',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    // 'email': $('#email').val(),
                    // 'password': $('#password').val(), //'123456789!aB'
                    // 'remember_me': remember_me.checked,
                    // '_token' : csrfToken
                    ...credentials
                })
            })
            const json = await response.json()
            resolve({response: json});
        } catch (error) {
            reject({error});
        }
    });
};

export const register = async () => {

}

export const logout = async () => {
    return new Promise(async (resolve, reject) => {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const response = await fetch(`/api/logout`, {
                method: 'delete',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            const json = await response.json()
            resolve({response: json});
        } catch (error) {
            reject({error});
        }
    });
    
}