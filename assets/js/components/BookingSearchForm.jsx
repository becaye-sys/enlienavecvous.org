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
                        <input type="checkbox" name={"aroundMe"} checked={search?.aroundMe && 'checked'} id={"aroundMe"} onChange={handleChange} className={"form-control"} />
                    </fieldset>
                </div>
                <div className="col-lg-3 col-md-6 col-sm-6">
                    <fieldset className="form-group">
                        <label htmlFor="department">Aucune disponibilité dans votre département ? Cherchez dans un autre département :</label>
                        <input value={search.department} type="text" name={"department"} id={"department"} className={"form-control"}/>
                    </fieldset>
                </div>
            </div>
        </form>
    )
}

export default BookingSearchForm