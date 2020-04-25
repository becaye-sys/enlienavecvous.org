import React, { useState, useEffect } from "react";
import ReactDOM from "react-dom";
import Pagination from "./components/Pagination";
import BookingConfirmation from "./components/BookingConfirmation";
import BookingRow from "./components/BookingRow";
import bookingApi, {createBooking, getBookings, updateBookingsByFilters} from "./services/bookingApi";
import bookingFilters from "./utils/bookingFilters";
import BookingSearchForm from "./components/BookingSearchForm";
import * as Sentry from '@sentry/browser';
Sentry.init({dsn: "https://13cbde40e40b44989821c2d5e9b8bafb@o346982.ingest.sentry.io/5211266"});

function PatientSearch(props) {
    const [currentPage, setCurrentPage] = useState(1);
    const [loading, setLoading] = useState(true);
    const [isConfirmed, setIsConfirmed] = useState(false);
    const [user, setUser] = useState({
        id: undefined
    });
    const [appoints, setAppoints] = useState([]);
    const [filtered, setFiltered] = useState([]);
    const [booking, setBooking] = useState({});
    const [search, setSearch] = useState({
        bookingDate: undefined,
        aroundMe: true,
        department: "",
    });

    const handlePageChange = page => {
        setCurrentPage(page);
    }

    const itemsPerPage = 10;

    const handleChange = ({currentTarget}) => {
        const { name, value } = currentTarget;
        setSearch({...search, [name]: value});
    };

    const handleSubmit = async event => {
        event.preventDefault();
        const { id: userId } = user;
        const currentBooking = JSON.parse(localStorage.getItem('booking'));
        console.log('ids:',currentBooking.id,userId);
        const response = await bookingApi.createBooking(currentBooking.id, userId);
        if (response.status !== 200) {
            console.log('Une erreur est survenue');
        } else {
            setIsConfirmed(true);
            setTimeout(resetSearch, 3000);
        }
    }

    const resetSearch = () => {
        setLoading(true);
        localStorage.getItem('booking') && localStorage.removeItem('booking');
        setIsConfirmed(false);
        setBooking({});
        setLoading(false);
    }

    const getCurrentUser = async () => {
        const userId = document.querySelector('div#patient_search_app').dataset.user;
        console.log(userId);
        if (userId !== undefined && userId !== '') {
            setUser({ id: userId });
        }
    }

    const filterWithTherapistDelay = (appoints) => {
        return appoints.filter(appoint => bookingFilters.filterWithTherapistDelay(appoint));
    }

    const getAppointments = async () => {
        const res = await bookingApi.getBookings();
        if (res.length > 0) {
            console.log('res:',res.length);
            const appoints = filterWithTherapistDelay(res);
            setAppoints(appoints);
            setLoading(false);
        }
    }

    const createPatientBooking = (appointId) => {
        console.log('appointId:',appointId);
        console.log('userId:',user.id);
        const booking = bookingFilters.filterById(appointId, appoints)[0];
        if (booking === {}) {
            console.log('Réservation vide');
            return;
        }
        setBooking(booking);
        console.log('booking:',booking);
        const saved = bookingFilters.setBookingToLocalStorage(booking);
    }

    const updateAppointsByUserFilters = () => {
        const updatedAppoints = bookingFilters.updateAppointsByFilters(appoints, search);
        setFiltered(updatedAppoints);
    }

    const updateBookingsByFilters = async () => {
        const bookings = await bookingApi.updateBookingsByFilters();
        if (bookings.length > 0) {
            console.log(bookings);
        }
    }

    const cancelBooking = () => {
        // delete local storage
        if (localStorage.getItem('booking')) {
            localStorage.removeItem('booking');
        }
        // delete booking state
        setBooking({});
    }

    const appointsToDisplay = filtered.length ? filtered : appoints;

    const paginatedAppoints = appointsToDisplay.length > itemsPerPage ? Pagination.getData(
        appointsToDisplay,
        currentPage,
        itemsPerPage
    ) : appointsToDisplay;

    useEffect(() => {
        getAppointments();
        getCurrentUser();
    },[]);

    useEffect(() => {
        updateAppointsByUserFilters();
    },[search]);

    return (
        <>
            {loading && <p>Chargement en cours...</p>}
            {!loading &&
            <div>
                {
                    (localStorage.getItem('booking') && booking !== {}) ?
                        <div className={"container mb-3"}>
                            <BookingConfirmation
                                loading={loading}
                                isConfirmed={isConfirmed}
                                handleSubmit={handleSubmit}
                                booking={JSON.parse(localStorage.getItem('booking'))}
                            />
                            <br/>
                            <button className={"btn btn-danger"} type="button" onClick={cancelBooking}>Annuler et prendre un autre rendez-vous</button>
                        </div> :
                        <div className="container mb-3">
                            <BookingSearchForm search={search} handleChange={handleChange} />
                            <div className="table-responsive js-rep-log-table">
                                <table className="table table-striped table-sm">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Thérapeute</th>
                                        <th>Date</th>
                                        <th>Début</th>
                                        <th>Fin</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    {
                                        paginatedAppoints.length > 0 &&
                                        <tbody>
                                        {paginatedAppoints.map(a => {

                                            return (
                                                <tr key={a.id}>
                                                    <BookingRow
                                                        booking={a}
                                                        createPatientBooking={createPatientBooking}
                                                    />
                                                </tr>
                                            )
                                        })}
                                        </tbody>
                                    }
                                </table>
                            </div>
                            {itemsPerPage < appointsToDisplay.length &&
                            <Pagination
                                currentPage={currentPage}
                                itemsPerPage={itemsPerPage}
                                onPageChanged={handlePageChange}
                                length={appointsToDisplay.length}
                            />
                            }
                        </div>
                }
            </div>
            }
        </>
    )
}

const rootElement = document.querySelector("#patient_search_app");
ReactDOM.render(<PatientSearch/>, rootElement);
