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
    const [user, setUser] = useState({
        id: undefined
    });
    const [appoints, setAppoints] = useState([]);
    const [filtered, setFiltered] = useState([]);
    const [booking, setBooking] = useState({});
    const [search, setSearch] = useState({
        bookingDate: undefined,
        aroundMe: "myTown",
        department: undefined,
        location: undefined
    });

    const handlePageChange = page => {
        setCurrentPage(page);
    }

    const itemsPerPage = 10;

    const handleChange = ({currentTarget}) => {
        const { name, value } = currentTarget;
        setSearch({...search, [name]: value});
    };

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

    const createPatientBooking = async (appointId) => {
        console.log('appointId:',appointId);
        console.log('userId:',user.id);
        setLoading(true);
        const booking = await bookingApi.createBooking(appointId, user.id);
        if (localStorage.getItem('booking')) {
            setBooking(booking);
            setLoading(false);
        }
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

    const cancelBooking = async () => {
        const booking = JSON.parse(localStorage.getItem('booking'));
        const response = await bookingApi.cancelBooking(booking.id);
        console.log(response);
        if (response === 200 || response === 204) {
            setBooking({});
            localStorage.getItem('booking') && localStorage.removeItem('booking');
            setLoading(false);
        }
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
                <div className="container-fluid mb-3">
                    <BookingSearchForm search={search} handleChange={handleChange} />
                </div>
                {
                    localStorage.getItem('booking') ?
                        <div className={"container"}>
                            <BookingConfirmation booking={JSON.parse(localStorage.getItem('booking'))} />
                            <br/>
                            <button className={"btn btn-danger"} type="button" onClick={cancelBooking}>Annuler et prendre un autre rendez-vous</button>
                        </div> :
                        <div className="container">
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
