export const formatDateForTable = (date) => {
    const timestamp = date.timestamp;
    return new Date(timestamp * 1000).toLocaleDateString("fr-FR");
}
export const formatDate = (date) => {
    const timestamp = date.timestamp;
    let d = new Date(timestamp * 1000),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();
    if (month.length < 2)
        {month = '0' + month;}
    if (day.length < 2)
        {day = '0' + day;}

    return [year, month, day].join('-');
}
export const formatDateStrict = (src) => {
    const timestamp = src.timestamp;
    const timestampDate = new Date(timestamp * 1000);
    return `${timestampDate.getFullYear()}-${timestampDate.getUTCMonth()}-${timestampDate.getUTCDate()}`;
}
export const formatTime = (time) => {
    const timestamp = time.timestamp;
    return new Date(timestamp * 1000).toLocaleTimeString("fr-FR").slice(0,5);
}