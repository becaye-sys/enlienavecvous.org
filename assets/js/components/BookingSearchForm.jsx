import React from "react";

function BookingSearchForm({ handleChange, search }) {
    return (
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
                        <label htmlFor="aroundMe">Autour de moi</label>
                        <select name="aroundMe" onChange={handleChange} id="aroundMe" className="form-control">
                            <option value="myTown">Ma commune</option>
                            <option value="myDepartment">Mon département</option>
                        </select>
                    </fieldset>
                </div>
                <div className="col-lg-3 col-md-6 col-sm-6">
                    <fieldset className="form-group">
                        <label htmlFor="department">Département</label>
                        <input value={search.department} type="text" name={"department"} id={"department"} className={"form-control"}/>
                    </fieldset>
                </div>
                <div className="col-lg-3 col-md-6 col-sm-6">
                    <fieldset className="form-group">
                        <label htmlFor="location">Code postal / Commune</label>
                        <input onChange={handleChange} value={search.location} type="text" name={"location"} id={"location"} className={"form-control"}/>
                    </fieldset>
                </div>
            </div>
        </form>
    )
}

export default BookingSearchForm