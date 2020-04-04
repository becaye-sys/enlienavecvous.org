import React, { useState, useEffect } from "react";
import ReactDOM from "react-dom";
import axios from "axios";
import {API_URL, customHeaders} from "./config";
import Pagination from "./components/Pagination";
import { formatDate, formatDateForTable, formatTime } from "./utils/DateUtils";

function PatientSearch(props) {
    const [currentPage, setCurrentPage] = useState(1);
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
        console.log('value:',value);
    };

    const getAppointments = async () => {
        console.log('fetch appoints');
        const res = await axios.get(`${API_URL}appointments`).then(response => {return response.data});
        if (res.length > 0) {
            setAppoints(res);
        }
    }

    useEffect(() => {
        getAppointments();
    },[]);

    useEffect(() => {
        updateAppointsByUserFilters();
    },[search]);

    const updateAppointsByUserFilters = () => {
        console.log('update appoints');
        console.log('search date value: ',search.bookingDate);
        console.log('search location value: ',search.location);

        if (search.bookingDate === undefined && search.location === undefined) {
            setFiltered(appoints);
        } else if (search.bookingDate !== undefined && search.location === undefined) {
            const updatedAppoints = appoints.filter(function (a) {
                return formatDate(a.bookingDate) === search.bookingDate;
            });
            setFiltered(updatedAppoints);
        } else if (search.bookingDate === undefined && search.location !== undefined) {
            console.log('case 2 ',search.location);
            const updatedAppoints = appoints.filter(a => {return a.location.toLowerCase().includes(search.location.toLowerCase())});
            setFiltered(updatedAppoints);
        } else {
            console.log(`case 3: ${search.bookingDate} + ${search.location}`);
            const updatedAppoints = appoints.filter(function (a) {
                return formatDate(a.bookingDate) === search.bookingDate && a.location.toLowerCase().includes(search.location.toLowerCase())
            });
            setFiltered(updatedAppoints);
        }
    }

    const getInfo = bookingDate => {
        const appoint = appoints.filter(a => a.bookingDate === bookingDate);
        console.log('info:',appoint);
    }

    const appointsToDisplay = filtered.length ? filtered : appoints;

    const paginatedAppoints = appointsToDisplay.length > itemsPerPage ? Pagination.getData(
        appointsToDisplay,
        currentPage,
        itemsPerPage
    ) : appointsToDisplay;

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
                                <input onChange={handleChange} value={search.location} type="text" name={"location"} id={"location"} className={"form-control"}/>
                            </fieldset>
                        </div>
                    </div>
                </form>
            </div>
            {
                paginatedAppoints.length > 0 &&
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
                            <tbody>
                            {paginatedAppoints.map(a => {
                                return (
                                    <tr key={a.id}>
                                        <td>
                                            <button onClick={() => getInfo(a.bookingDate)} className={"btn btn-outline-primary"}>
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
                        </table>
                    </div>
                    <Pagination
                        currentPage={currentPage}
                        itemsPerPage={itemsPerPage}
                        onPageChanged={handlePageChange}
                        length={appoints.length} />
                </div>
            }
        </div>
    )
}


const rootElement = document.querySelector("#patient_search_app");
ReactDOM.render(<PatientSearch/>, rootElement);
