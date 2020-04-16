// @ts-ignore
import React, { useState, useEffect } from "react";
// @ts-ignore
import styles from "./CitySelect.module.css";
import { countryListJson } from "./countryList";
import { fetchSuggestions } from "../services/fetchSuggestions";

interface Props {
    onSubmit: (d: string) => void;
}

export const CitySelect: React.FC<Props> = ({ onSubmit }) => {
    // const [regionList, setregionList] = useState(
    //   [] as { name: string; code: string }[]
    // );
    const [selectedCountry, setselectedCountry] = useState(
        "" as string | undefined
    );
    // const [selectedRegion, setselectedRegion] = useState(
    //   "" as string | undefined
    // );
    const [cityStringRequest, setcityStringRequest] = useState(
        "" as string | undefined
    );
    const [selectedCity, setselectedCity] = useState("" as string | undefined);
    const [selectedCode, setselectedCode] = useState("" as string | undefined);

    const [suggestions, setsuggestions] = useState([] as any[]);

    useEffect(() => {
        if (cityStringRequest && cityStringRequest.length >= 3) {
            loadSuggestions();
        } else {
            setsuggestions([]);
        }
    }, [cityStringRequest]);

    useEffect(() => {
        // setselectedRegion("");
        setcityStringRequest("");
        setselectedCity("");
        setselectedCode("");
        setsuggestions([]);
    }, [selectedCountry]);

    useEffect(() => {
        setsuggestions([]);
    }, [selectedCity]);

    const onCountrySelect = (d: React.ChangeEvent<HTMLSelectElement>) => {
        setselectedCountry(d.currentTarget.value);
        //@ts-ignore
        // setregionList(regionListJson[d.currentTarget.value]);
        // If region has changed
    };

    // const onRegionSelect = (d: React.ChangeEvent<HTMLSelectElement>) => {
    //   setselectedRegion(d.currentTarget.value);
    //   setcityStringRequest("");
    // };

    const onCityChange = (d: React.ChangeEvent<HTMLInputElement>) => {
        setselectedCity("");
        setcityStringRequest(d.currentTarget.value);
    };

    const selectCity = (d: any) => {
        setselectedCity(d.ville);
        setselectedCode(d.cp);
        onSubmit(d);
    };

    const reset = () => {
        setcityStringRequest("");
        // setselectedRegion("");
        setselectedCountry("");
        setselectedCity("");
        setselectedCode("");
        setsuggestions([]);
    };

    const loadSuggestions = async () => {
        const res = await fetchSuggestions(selectedCountry!, cityStringRequest!);
        if (res.message) {
            // handle error here
            alert(res.message);
        } else {
            res.results && setsuggestions(res.results);
        }
    };
    const inputCityValue = selectedCity || cityStringRequest;
    return (
        <div className={styles["container"]}>
            <select
                className={styles["country-select"]}
                onChange={onCountrySelect}
                value={selectedCountry}
            >
                <option value={""} key={`slected-country-${0}`}>
                    Select a country
                </option>
                {countryListJson.map((d, i) => (
                    <option value={d.name} key={`slected-country-${i}`}>
                        {d.name}
                    </option>
                ))}
            </select>
            {/* <select
        disabled={!(selectedCountry && regionList)}
        className={styles["region-select"]}
        onChange={onRegionSelect}
        value={selectedRegion}
      >
        <option value={""}>Select a region</option>
        {regionList.map((d, i) => (
          <option value={d.code} key={`selected-region-${i}`}>
            {d.name}
          </option>
        ))}
      </select> */}
            <div className={styles["input-container"]}>
                <input
                    disabled={!selectedCountry}
                    type="text"
                    className={styles["city-input"]}
                    placeholder="Ville"
                    value={inputCityValue}
                    onChange={onCityChange}
                />
                <div className={styles["suggestions-container"]}>
                    {suggestions &&
                    suggestions.map((d, i) => (
                        <div
                            className={styles["suggestion-item"]}
                            onClick={() => selectCity(d)}
                            key={`suggested-city-${i}`}
                        >
                            {d.ville} - {d.cp}
                        </div>
                    ))}
                </div>
            </div>
            <div className={styles["input-container"]}>
                <input
                    disabled={true}
                    type="text"
                    className={styles["city-input"]}
                    placeholder="CP"
                    value={selectedCode}
                />
            </div>
            <div className={styles["reset-container"]} onClick={reset}>
                X
            </div>
            {selectedCity && (
                <div className={styles["ok-container"]} onClick={reset}>
                    OK
                </div>
            )}
            {!selectedCity && (
                <div className={styles["not-ok-container"]} onClick={reset}>
                    NOT OK
                </div>
            )}
        </div>
    );
};