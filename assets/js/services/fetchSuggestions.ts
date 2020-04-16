const API_LOGIN = "onestlapourvous";
const API_KEY = "so4c0d00de65b6aae5842f3e6f4a32040c0f5f7058";

const countryMapping: any = {
    France: "fr",
    Belgique: "be",
    Suisse: "ch",
    Luxembourg: "lu"
};

//const defaultUrl = `http://www.citysearch-api.com/${countryMapping[country]}/city?login=${API_LOGIN}&apikey=${API_KEY}&ville=${stringRequest}`;

export const fetchSuggestions = async (
    country: string,
    stringRequest: string
) => {
    return fetch(
        `http://127.0.0.1:8000/api/get-api?country=${countryMapping[country]}&city=${stringRequest}`
    ).then(d => {
        if (d.status === 200) {
            return d.json();
        } else {
            return new Error("Error fetching cities");
        }
    });
};