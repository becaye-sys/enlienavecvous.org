import React from "react";
import {formatDateForTable, formatTime} from "../utils/DateUtils";

function BookingRow({ createPatientBooking, booking, user }) {
    return (
        <tr key={booking.id}>
            <td>
                <button onClick={() => createPatientBooking(booking.id, user.userId)} className={"btn btn-outline-primary"}>
                    Réserver
                </button>
            </td>
            <td>{booking.therapist?.displayName ?? booking.therapist?.firstName + " " + booking.therapist?.lastName} - {booking.therapist?.email}</td>
            <td>{formatDateForTable(booking.bookingDate)}</td>
            <td>{formatTime(booking.bookingStart)}</td>
            <td>{formatTime(booking.bookingEnd)}</td>
            <td>{booking.location}</td>
        </tr>
    )
}

export default BookingRow