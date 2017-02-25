<!DOCTYPE html>
<html>
<head>
    <title></title>
    <style>
        .block {
            width: 300px;
            padding-right: 10px;
            float: left;
        }
        .main {
            width: 930px;
            margin: 0 auto;
        }
    </style>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
    <script src="script.js"></script>
</head>
<body>
    <div class="main">
        <div>
            <form action="/?r=add-query" id="form-add-query" method="POST">
                <input type="text" name="raw_query" style="width: 200px;">
                <button type="submit">Add</button>
            </form>
        </div>
        <br>
        <br>
        <div class="block">
            <div id="block-queries"></div>
            <img id="block-queries-loading-img" src="Loading_icon.gif" alt="loading..." style="" />
        </div>
        <div class="block">
            <div id="block-summary" style="display: none">
                <h3 id="block-summary-name"></h3>
                <br>
                Результатов в базе: <span id="block-summary-cnt"></span>
                <br>
                Средний рейтинг: <span id="block-summary-avg-rating"></span>
            </div>
            <img id="block-summary-loading-img" src="Loading_icon.gif" alt="loading..." style="display: none" />
        </div>
        <div class="block">
            <div id="block-results" style="display: none"></div>
            <img id="block-results-loading-img" src="Loading_icon.gif" alt="loading..." style="display: none" />
        </div>
        <div style="clear: both"></div>
    </div>
</body>
</html>