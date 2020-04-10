import React, { useState, useEffect } from "react";
import ReactDOM from "react-dom";
import axios from "axios";
import {API_URL, customHeaders} from "./config";
import Pagination from "./components/Pagination";
import { formatDate, formatDateForTable, formatTime, getArrayDate, getArrayTime } from "./utils/DateUtils";
import moment, {now} from "moment";

function PatientSearch(props) {
    const [currentPage, setCurrentPage] = useState(1);
    const [loading, setLoading] = useState(true);
    const [user, setUser] = useState({
        userId: undefined
    });
    const [appoints, setAppoints] = useState([]);
    const [filtered, setFiltered] = useState([]);
    const [booking, setBooking] = useState({});
    const [search, setSearch] = useState({
        bookingDate: undefined,
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
        const userId = document.querySelector('h1.h2').dataset.userId;
        if (userId !== undefined && userId !== '') {
            setUser({...user, userId})
        }
    }

    const getAppointments = async () => {
        const res = await axios.get(`${API_URL}appointments`).then(response => {return response.data});
        if (res.length > 0) {
            //console.log('res:',res);
            const appoints = filterWithTherapistDelay(res);
            setAppoints(appoints);
            setLoading(false);
        }
    }

    const filterWithTherapistDelay = (appoints) => {
        const filtered = appoints.filter(a => {
            const nowDate = moment().format('YYYY-MM-DD');
            //console.log('nowDate:',nowDate);
            if (nowDate === formatDate(a.bookingDate)) {
                //console.log('delay param:');
                console.log("créneau pour aujourd'hui");
                const arrayTime = getArrayTime(a.bookingStart);
                //console.log('arrayTimeBooking:',arrayTime);
                const nowTime = moment();
                console.log('nowTime', nowTime);
                const targetTime = moment().hours(arrayTime[0]).minutes(arrayTime[1]);
                console.log('targetTime:',targetTime);
                let startTime = moment([arrayTime[0], arrayTime[1]]).format('HH:mm');
                //console.log('start time:',startTime);
                const delay = targetTime.diff(nowTime, 'hours');
                console.log('delay:',delay);
                console.log('différence:',targetTime - nowTime);
                if ((targetTime > nowTime) && delay >= 12) {
                    return a;
                }
            } else if (nowDate < formatDate(a.bookingDate)) {
                console.log("créneau pour plus tard");
                return a;
            } else {
                console.log("créneau passé");
            }
            //return nowDate !== formatDate(a.bookingDate);
        });
        return filtered;
    }

    const createPatientBooking = async (appointId, userId) => {
        setLoading(true);
        const booking = await axios.post(
            `${API_URL}create/booking/${appointId}/${userId}`)
            .then(response => {
                console.log('create booking response:',response);
                if (localStorage.getItem('booking')) {
                    localStorage.removeItem('booking');
                    localStorage.setItem('booking', JSON.stringify(response.data));
                } else {
                    localStorage.setItem('booking', JSON.stringify(response.data));
                }
                return response.data
            });
        console.log('booking:',booking);
        setBooking(booking);
        setLoading(false);
    }

    const updateAppointsByUserFilters = () => {
        if (search.bookingDate === undefined && search.location === undefined) {
            setFiltered(appoints);
        } else if (search.bookingDate !== undefined && (search.location === undefined || search.location === '')) {
            const updatedAppoints = appoints.filter(function (a) {
                return formatDate(a.bookingDate) === search.bookingDate;
            });
            setFiltered(updatedAppoints);
        } else if ((search.bookingDate === undefined || search.bookingDate === '') && search.location !== undefined) {
            //console.log('location only changed');
            const updatedAppoints = appoints.filter(a => {return a.location.toLowerCase().includes(search.location.toLowerCase())});
            setFiltered(updatedAppoints);
        } else {
            const updatedAppoints = appoints.filter(function (a) {
                return formatDate(a.bookingDate) === search.bookingDate && a.location.toLowerCase().includes(search.location.toLowerCase())
            });
            setFiltered(updatedAppoints);
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
        <div>
            <div className="container-fluid mb-3">
                <form>
                    <div className="row">
                        <div className="col-lg-2 col-md-6 col-sm-6">
                            <fieldset className="form-group">
                                <label htmlFor="bookingDate">Date</label>
                                <input onChange={handleChange} value={search.bookingDate} type="date" name={"bookingDate"} id={"bookingDate"} className={"form-control"}/>
                            </fieldset>
                        </div>
                        <div className="col-lg-3 col-md-6 col-sm-6">
                            <fieldset className="form-group">
                                <label htmlFor="department">Département</label>
                                <input value={""} type="text" name={"department"} id={"department"} className={"form-control"}/>
                            </fieldset>
                        </div>
                        <div className="col-lg-3 col-md-6 col-sm-6">
                            <fieldset className="form-group">
                                <label htmlFor="location">Code postal / Commune</label>
                                <input onChange={(event) => handleChange(event)} value={search.location} type="text" name={"location"} id={"location"} className={"form-control"}/>
                            </fieldset>
                        </div>
                        <div className="col-lg-3 col-md-6 col-sm-6">
                            <fieldset className="form-group">
                                <label htmlFor="aroundMe">Autour de moi</label>
                                <select name="aroundMe" id="aroundMe" className="form-control">
                                    <option value="department">Mon département</option>
                                    <option value="town">Ma commune</option>
                                </select>
                            </fieldset>
                        </div>
                    </div>
                </form>
            </div>
            {
                localStorage.getItem('booking') ?
                    <BookingConfirmation booking={booking} /> :
                    <div className="container">
                        <div className="table-responsive js-rep-log-table">
                            <table className="table table-striped table-sm">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>id</th>
                                    <th>Date</th>
                                    <th>Début</th>
                                    <th>Fin</th>
                                    <th>Lieu</th>
                                    <th></th>
                                </tr>
                                </thead>
                                {
                                    paginatedAppoints.length > 0 &&
                                    <tbody>
                                    {paginatedAppoints.map(a => {

                                        return (
                                            <tr key={a.id}>
                                                <td>
                                                    <button onClick={() => createPatientBooking(a.id, user.userId)} className={"btn btn-outline-primary"}>
                                                        Réserver
                                                    </button>
                                                </td>
                                                <td>{a.id}</td>
                                                <td>{formatDateForTable(a.bookingDate)}</td>
                                                <td>{formatTime(a.bookingStart)}</td>
                                                <td>{formatTime(a.bookingEnd)}</td>
                                                <td>{a.location}</td>
                                            </tr>
                                        )
                                    })}
                                    </tbody>
                                }
                            </table>
                            {loading && <p>Chargement en cours...</p>}
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
    )
}

export function BookingConfirmation(props) {
    const [appoint, setAppoint] = useState({});
    const [loading, setLoading] = useState(false);
    const [isConfirmed, setIsConfirmed] = useState(false);

    useEffect(() => {
        setLoading(true);
        if (booking.length === undefined) {
            const appoint = JSON.parse(localStorage.getItem('booking'));
            setAppoint(appoint);
        }
        setLoading(false);
        console.log(booking.length);
    },[]);

    const handleSubmit = async event => {
        event.preventDefault();
        setLoading(true);
        const status = await axios.post(`${API_URL}confirm/booking/${booking.id}`).then(response => {
            return response.status
        });
        if (status === 200) {
            localStorage.removeItem('booking');
            setIsConfirmed(true);
        }
        setLoading(false);
    }

    var booking = props.booking.length === undefined ? appoint : booking

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
                                    {booking.bookingDate && formatDateForTable(booking.bookingDate)} avec {booking.therapist?.firstName} {booking.therapist?.lastName}
                                    {booking.therapist?.zipCode}
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


const rootElement = document.querySelector("#patient_search_app");
ReactDOM.render(<PatientSearch/>, rootElement);
