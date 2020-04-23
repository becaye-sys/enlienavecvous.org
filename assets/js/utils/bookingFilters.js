import moment from "moment";
import {formatDate, getArrayTime} from "./DateUtils";

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

function updateAppointsByFilters(appoints, search) {
    if (search.bookingDate === undefined && search.location === undefined) {
        return appoints;
    } else if (search.bookingDate !== undefined && (search.location === undefined || search.location === '')) {
        return appoints.filter(function (a) {
            return formatDate(a.bookingDate) === search.bookingDate;
        });
    } else if ((search.bookingDate === undefined || search.bookingDate === '') && search.location !== undefined) {
        return appoints.filter(a => {
            return a.location.toLowerCase().includes(search.location.toLowerCase())
        });
    } else {
        return appoints.filter(function (a) {
            return formatDate(a.bookingDate) === search.bookingDate
                && a.location.toLowerCase().includes(search.location.toLowerCase())
        });
    }
}

export default {
    filterWithTherapistDelay,
    updateAppointsByFilters
}