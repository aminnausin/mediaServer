// TODO: json from response does not include status code. Find a modular way to handle that

export const login = async (credentials) => {
    try {
        const response = await fetch(`/api/login`, {
            method: 'post',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                ...credentials
            })
        })
        const json = await response.json();
        return Promise.resolve({response: json});
    } catch (error) {
        return Promise.reject({error});
    }
};

export const register = async (credentials) => {
    try {
        const response = await fetch(`/api/register`, {
            method: 'post',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                ...credentials
            })
        })
        const json = await response.json();
        return Promise.resolve({response: json});
    } catch (error) {
        return Promise.reject({error});
    }
}

export const logout = async () => {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        // const localToken = localStorage.getItem('auth-token');
        const response = await fetch(`/api/logout`, {
            method: 'delete',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
                // 'Authorization': "Bearer " + localToken,
            }
        })
        const json = await response.json();
        return Promise.resolve({response: json});
    } catch (error) {
        return Promise.reject({error});
    }
}