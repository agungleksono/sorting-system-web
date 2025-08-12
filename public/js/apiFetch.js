window.apiFetch = async function (url, method = 'GET', data = null, token = '') {
    const options = {
        method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
    };

    if (token) {
        options.headers['Authorization'] = `Bearer ${token}`;
    }

    if (data) {
        options.body = JSON.stringify(data);
    }

    try {
        const response = await fetch(url, options);
        const result = await response.json();
    
        if (!response.ok) {
            throw new Error(result.meta?.message || result.message || 'Something went wrong.');
        }
    
        return result;
    } catch (error) {
        throw error;
    }
};