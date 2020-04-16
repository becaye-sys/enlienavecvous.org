import React, {useEffect, useState} from "react";
import axios from "axios";
import {API_URL} from "../config";
import {formatDateForTable} from "../utils/DateUtils";

function BookingConfirmation(props) {
    const [appoint, setAppoint] = useState({});
    const [loading, setLoading] = useState(false);
    const [isConfirmed, setIsConfirmed] = useState(false);

    useEffect(() => {
        setLoading(true);
        const appoint = JSON.parse(localStorage.getItem('booking'));
        setAppoint(appoint);
        setLoading(false);
    },[]);

    const handleSubmit = async event => {
        event.preventDefault();
        setLoading(true);
        const status = await axios.post(`${API_URL}confirm/booking/${appoint.id}`).then(response => {
            return response.status
        });
        if (status === 200) {
            localStorage.removeItem('booking');
            setIsConfirmed(true);
        }
        setLoading(false);
    }

    return (
        <div>
            {loading && <div className="container"><p>Chargement en cours...</p></div>}
            {
                !loading &&
                <div className="container">
                    {
                        isConfirmed ?
                            <div className="alert alert-success">
                                Rendez-vous confirmé !
                            </div> :
                            <div>
                                <h2>Demande de rendez-vous</h2>
                                {appoint.bookingDate && formatDateForTable(appoint.bookingDate)} avec {appoint.therapist?.firstName} {appoint.therapist?.lastName}
                                {appoint.therapist?.zipCode}
                                <div className="alert alert-warning">
                                    En cas d'annulation, merci de prévenir votre thérapeute au plus vite en cliquant sur le bouton d'annulation disponible dans vos rendez-vous.
                                </div>
                                <form onSubmit={handleSubmit}>
                                    <button className="btn btn-primary" type="submit">Confirmer mon rendez-vous</button>
                                </form>
                            </div>
                    }
                </div>
            }
        </div>
    )
}

export default BookingConfirmation