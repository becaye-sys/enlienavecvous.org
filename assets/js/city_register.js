import React from "react";
import ReactDOM from "react-dom"
import {CitySelect} from "./components/CitySelect";


const rootElement = document.querySelector("#city_register");
ReactDOM.render(<CitySelect onSubmit={(d) => console.log(d)}/>, rootElement);
