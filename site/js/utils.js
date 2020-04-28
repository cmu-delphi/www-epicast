//Submit the form
function submit(formID) {
  $('#' + formID).submit();
}

//Follow the url
function navigate(url) {
  window.location = url;
}

//Convert number to string with commas - http://stackoverflow.com/a/2901298
function numberWithCommas(x) {
  return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// format an epiweek as YYYYwWW
function formatEpiweek(epiweek) {
  let year = Math.floor(epiweek / 100);
  let week = epiweek % 100;
  let zeroPaddedWeek = '0' + week;
  return year + 'w' + zeroPaddedWeek.substring(zeroPaddedWeek.length - 2);
}

// get the formatted version of the latest fluview issue
function getLatestIssueFormatted(callback) {
  function epidataCallback(result, message, epidata) {
    if (result === 1) {
      callback(formatEpiweek(epidata[0].latest_issue));
    }
  }
  Epidata.fluview_meta(epidataCallback);
}
