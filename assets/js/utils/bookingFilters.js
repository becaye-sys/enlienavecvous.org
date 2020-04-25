import moment from "moment";
import {formatDate, formatDateReverse, getArrayTime} from "./DateUtils";

function filterWithTherapistDelay(a) {
    const nowDate = moment().format('YYYY-MM-DD');
    if (nowDate === formatDate(a.bookingDate)) {
        const arrayTime = getArrayTime(a.bookingStart);
        const nowTime = moment();
        const targetTime = moment().hours(arrayTime[0]).minutes(arrayTime[1]);
        const delay = targetTime.diff(nowTime, 'hours');
        if ((targetTime > nowTime) && delay >= 12) {
            return a;
        }
    } else if (nowDate < formatDate(a.bookingDate)) {
        return a;
    } else {
        console.log("créneau passé");
    }
}

function filterById(id, appoints) {
    return appoints.filter(function (appoint, i) {
        return appoint.id === id;
    });
}

function setBookingToLocalStorage(booking) {
    try {
        if (localStorage.getItem('booking')) {
            localStorage.removeItem('booking');
            localStorage.setItem('booking', JSON.stringify(booking));
        } else {
            localStorage.setItem('booking', JSON.stringify(booking));
        }
        return true;
    } catch (e) {
        console.log(e);
        return false;
    }
}

function updateAppointsByFilters(appoints, search) {
    if (search.bookingDate !== undefined) {
        return appoints.filter(function (a) {
            return formatDateReverse(a.bookingDate) === search.bookingDate;
        });
    } else if (search.bookingDate !== undefined && search.department !== undefined) {
        return appoints.filter(function (a) {
            return a.therapist?.department?.name.toLowerCase().includes(search.department.toLowerCase());
        });
    } else {
        return appoints;
    }
}

export default {
    filterWithTherapistDelay,
    filterById,
    setBookingToLocalStorage,
    updateAppointsByFilters
}