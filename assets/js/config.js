const API_ENV_URL = process.env.API_URL;

function apiUrlRegex(URL) {
    console.log('url:',URL);
    return URL.replace("'", "");
}

export const API_URL = apiUrlRegex(API_ENV_URL);

export const customHeaders = {
    'content-type': 'application/json',
    'access-control-allow-origin': '*',
    'accept': '*'
};