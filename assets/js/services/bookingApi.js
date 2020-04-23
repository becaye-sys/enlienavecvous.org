import axios from "axios";
import {API_URL} from "../config";

async function getBookings() {
    return await axios
        .get(`${API_URL}appointments`)
        .then(response => {return response.data});
}

async function createBooking(appointId, userId) {
    return await axios.get(
        `${API_URL}create/booking?appoint=${appointId}&user=${userId}`
    )
        .then(response => {
            try {
                if (localStorage.getItem('booking')) {
                    localStorage.removeItem('booking');
                    localStorage.setItem('booking', JSON.stringify(response.data));
                } else {
                    localStorage.setItem('booking', JSON.stringify(response.data));
                }
                console.log(response.data);
                return response.data
            } catch (e) {
                console.log(e);
            }
        })
        .catch(error => {
            console.log('erreur lors de la création de la réservation :',error);
        })
        ;
}

async function confirmBooking(appoint) {
    return await axios.get(`${API_URL}confirm/booking/${appoint.id}`).then(response => {
        return response.status
    });
}

async function cancelBooking(id) {
    return await axios.get(`${API_URL}cancel/booking?id=${id}`).then(response => {
        return response.status
    });
}

async function updateBookingsByFilters(search) {
    return await axios
        .post(`${API_URL}bookings-filtered`, {...search})
        .then(response => {
            return response.data;
        });
}

export default {
    getBookings,
    createBooking,
    confirmBooking,
    cancelBooking,
    updateBookingsByFilters
}