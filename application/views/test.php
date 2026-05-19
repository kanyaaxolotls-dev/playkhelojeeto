<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Live Update</title>
<style>
    #flyingValue {
        font-weight: bold;
        color: green;
        animation: fadeIn 1s;
    }
    #crashValue {
        color: red;
        font-weight: bold;
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    #center {
        text-align: center;
    }
    #results {
        margin-top: 20px;
    }
    .result-item {
        font-size: 18px;
        margin: 5px 0;
    }
</style>
</head>
<body>
<div id="center">
    <h2>Period Id : <span id="period">Loading...</span></h2>
</div>
<div>
    <h2>Flying</h2>
    <div id="flyingValue">Loading...</div>
</div>
<div>
    <h2>Crash</h2>
    <div id="crashValue">Loading...</div>
</div>
<div id="results">
    <h2>Recent Results</h2>
    <div id="resultsList">Loading...</div>
</div>

<script>
var previousFlyingValue = NaN;  

function fetchData() {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "https://gamechanger.otomet.in/api/Aviator/add2", true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var response    = JSON.parse(xhr.responseText);
            var flyingValue = parseFloat(response.flying);
            var crashValue  = parseFloat(response.crash_value);
            var periodId    = response.period;
            var results     = response.results;
            document.getElementById('period').textContent = periodId;
            updateFlyingValue(flyingValue);
            updateCrashValue(crashValue);
            updateResults(results);
        }
    };
    xhr.send();
}

function updateFlyingValue(newValue) {
    var flyingValueElement = document.getElementById('flyingValue');
    if (isNaN(previousFlyingValue)) {
        flyingValueElement.textContent = newValue.toFixed(2) + 'X';
    } else {
        var increment = (newValue - previousFlyingValue) / 100;
        var currentValue = previousFlyingValue;
        var interval = setInterval(function() {
            currentValue += increment;
            flyingValueElement.textContent = currentValue.toFixed(2) + 'X';
            if (currentValue >= newValue) {
                clearInterval(interval);
            }
        }, 10);
    }
    previousFlyingValue = newValue;
}

function updateCrashValue(newValue) {
    var crashValueElement = document.getElementById('crashValue');
    crashValueElement.textContent = newValue.toFixed(2) + 'X';
}

function updateResults(results) {
    var resultsList       = document.getElementById('resultsList');
    resultsList.innerHTML = ''; 
    results.forEach(function(result) {
        var div = document.createElement('div');
        div.classList.add('result-item');
        div.textContent = result.winning + 'X';  
        resultsList.appendChild(div);
    });
}

setInterval(fetchData, 2000);
</script>
</body>
</html>
