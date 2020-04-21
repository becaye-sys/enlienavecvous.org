import React, { useState, useEffect } from "react";
import ReactDOM from "react-dom";
import axios from "axios";
import {API_URL} from "./config";

export function Geolocation() {
    const [selection, setSelection] = useState({
        country: "fr",
        department: "",
        city: "",
        citySearch: ""
    });

    const [departments, setDepartments] = useState([]);
    const [cities, setCities] = useState([]);
    const [filteredCities, setFilteredCities] = useState([]);

    const handleChange = ({currentTarget}) => {
        const { name, value } = currentTarget;
        setSelection({...selection, [name]: value});
    };

    const getDepartmentsByCountry = async () => {
        const departs = await axios
            .get(`${API_URL}departments-by-country?country=${selection.country}`)
            .then(response => {
                return response.data;
            });
        setDepartments([]);
        setDepartments(departs.length > 0 && departs);
    }

    const getCitiesByDepartment = async () => {
        const cities = await axios
            .get(`${API_URL}towns-by-department?department=${selection.department}`)
            .then(response => {
                return response.data;
            });
        setCities([]);
        setCities(cities.length > 0 && cities);
    }

    const getFilteredCities = () => {
        setFilteredCities([]);
        const filtered = cities.filter(c =>
            c.name.toLowerCase().includes(selection.citySearch)
            || c.code.includes(selection.citySearch)
        );
        console.log(filtered);
        setFilteredCities(filtered);
    }

    useEffect(() => {
        getDepartmentsByCountry();
    }, [selection.country]);

    useEffect(() => {
        getCitiesByDepartment();
    }, [selection.department]);

    useEffect(() => {
        getFilteredCities();
    }, [selection.citySearch]);

    return (
        <>
            <div className="row">
                <div className="form-group col-md-6">
                    <select onChange={handleChange} name="country" id="country" className={"form-control"}>
                        <option value="">Sélectionnez votre pays</option>
                        <option value="fr">France</option>
                        <option value="be">Belgique</option>
                        <option value="lu">Luxembourg</option>
                        <option value="ch">Suisse</option>
                    </select>
                </div>
                <div className="form-group col-md-6">
                    <select onChange={handleChange} name="department" id="department" className={"form-control"}>
                        <option value="">Sélectionnez votre département</option>
                        {departments &&
                        departments.map((depart, key) => {
                            return (
                                <option key={key} value={depart.id}>{depart.name}</option>
                            )
                        })
                        }
                    </select>
                </div>
            </div>
            <div className="row">
                <div className="form-group col-md-6">
                    <label htmlFor="citySearch">Recherchez votre ville/commune/code postal</label>
                    <input onChange={handleChange} type="text" name={"citySearch"} id={"citySearch"} className={"form-control"}/>
                </div>
                {
                    selection.citySearch.length >= 2 &&
                    <div className="form-group col-md-6">
                        <label htmlFor="town">Sélectionnez ensuite votre localisation</label>
                        <select name="town" id="town" className={"form-control"}>
                            <option value="">Sélectionnez votre localisation</option>
                            {
                                filteredCities.length && filteredCities.map((city, key) => (
                                    <option key={key} value={city.id}>{city.name}</option>
                                ))
                            }
                        </select>
                    </div>
                }
            </div>
        </>
    )
}


const rootElement = document.querySelector("#geolocation");
ReactDOM.render(<Geolocation/>, rootElement);
