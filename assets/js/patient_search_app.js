import React, { useState, useEffect } from "react";
import ReactDOM from "react-dom";
import axios from "axios";
import {API_URL, customHeaders} from "./config";
import Pagination from "./components/Pagination";
import { formatDate, formatDateForTable, formatTime, getArrayDate, getArrayTime } from "./utils/DateUtils";
import  moment from "moment";

function PatientSearch(props) {
    const [currentPage, setCurrentPage] = useState(1);
    const [loading, setLoading] = useState(true);
    const [appoints, setAppoints] = useState([]);
    const [filtered, setFiltered] = useState([]);
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

    const getAppointments = async () => {
        const res = await axios.get(`${API_URL}appointments`).then(response => {return response.data});
        if (res.length > 0) {
            console.log('res:',res);
            const appoints = filterWithTherapistDelay(res);
            setAppoints(appoints);
            setLoading(false);
        }
    }

    const filterWithTherapistDelay = (appoints) => {
        const filtered = appoints.filter(a => {
            const nowDate = moment().format('YYYY-MM-DD');
            console.log('nowDate:',nowDate);
            if (nowDate === formatDate(a.bookingDate)) {
                console.log('delay param:');
                console.log("c'est pour aujourd'hui");
                const arrayTime = getArrayTime(a.bookingStart);
                console.log('arrayTimeBooking:',arrayTime);
                const nowTime = moment();
                console.log('nowTime', nowTime);
                const targetTime = moment().hours(arrayTime[0]).minutes(arrayTime[1]);
                console.log('targetTime:',targetTime);
                let startTime = moment([arrayTime[0], arrayTime[1]]).format('HH:mm');
                console.log('start time:',startTime);
                const delay = targetTime.diff(nowTime, 'hours');
                console.log('delay:',delay);
                if (delay >= 12) {
                    return a;
                }
            } else {
                return a;
            }
            //return nowDate !== formatDate(a.bookingDate);
        });
        return filtered;
    }

    useEffect(() => {
        getAppointments();
    },[]);

    useEffect(() => {
        updateAppointsByUserFilters();
    },[search]);

    const updateAppointsByUserFilters = () => {
        console.log('search changed');
        console.log('date:',search.bookingDate);
        if (search.bookingDate === undefined && search.location === undefined) {
            setFiltered(appoints);
        } else if (search.bookingDate !== undefined && (search.location === undefined || search.location === '')) {
            const updatedAppoints = appoints.filter(function (a) {
                return formatDate(a.bookingDate) === search.bookingDate;
            });
            setFiltered(updatedAppoints);
        } else if ((search.bookingDate === undefined || search.bookingDate === '') && search.location !== undefined) {
            console.log('location only changed');
            const updatedAppoints = appoints.filter(a => {return a.location.toLowerCase().includes(search.location.toLowerCase())});
            setFiltered(updatedAppoints);
        } else {
            const updatedAppoints = appoints.filter(function (a) {
                return formatDate(a.bookingDate) === search.bookingDate && a.location.toLowerCase().includes(search.location.toLowerCase())
            });
            setFiltered(updatedAppoints);
        }
    }

    const getInfo = id => {
        const appoint = appoints.filter(a => {return a.id === id});
        alert("Cette fonctionnalité n'est pas encore prête");
    }

    const appointsToDisplay = filtered.length ? filtered : appoints;

    const paginatedAppoints = appointsToDisplay.length > itemsPerPage ? Pagination.getData(
        appointsToDisplay,
        currentPage,
        itemsPerPage
    ) : appointsToDisplay;

    console.log(filtered.length);

    return (
        <div>
            <div className="container-fluid mb-3">
                <form>
                    <div className="row">
                        <div className="col-lg-4 col-md-6 col-sm-6">
                            <fieldset className="form-group">
                                <label htmlFor="bookingDate">Date</label>
                                <input onChange={handleChange} value={search.bookingDate} type="date" name={"bookingDate"} id={"bookingDate"} className={"form-control"}/>
                            </fieldset>
                        </div>
                        <div className="col-lg-4 col-md-6 col-sm-6">
                            <fieldset className="form-group">
                                <label htmlFor="location">Code postal / Commune</label>
                                <input onChange={(event) => handleChange(event)} value={search.location} type="text" name={"location"} id={"location"} className={"form-control"}/>
                            </fieldset>
                        </div>
                    </div>
                </form>
            </div>
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
                                            <button onClick={() => getInfo(a.id)} className={"btn btn-outline-primary"}>
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
        </div>
    )
}


const rootElement = document.querySelector("#patient_search_app");
ReactDOM.render(<PatientSearch/>, rootElement);
