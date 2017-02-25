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
    <script>
        function load_queries()
        {
            $('#block-queries').hide();
            $('#block-queries-loading-img').show();

            $.ajax({
                url: '/?r=get-queries',
                success: function(data){
                    $('#block-queries').html(data);
                    $('#block-queries').show();
                    $('#block-queries-loading-img').hide();
                }
            })
        }

        $(function(){
            load_queries();

            $('#block-queries').on('click','.link-query',function(e){
                e.preventDefault();

                $('#block-summary-loading-img,#block-results-loading-img').show();
                $('#block-summary,#block-results').hide();

                $.ajax({
                    url: $(this).attr('href'),
                    dataType: 'json',
                    success: function(data){
                        $('#block-summary-name').text(data['summary']['name']);
                        $('#block-summary-cnt').text(data['summary']['cnt']);
                        $('#block-summary-avg-rating').text(data['summary']['avg_rating']);

                        $('#block-results').html(data['results']);

                        $('#block-summary-loading-img,#block-results-loading-img').hide();
                        $('#block-summary,#block-results').show();
                    }
                });
            });

            $('#form-add-query').submit(function(e){
                e.preventDefault();

                var data = $(this).serialize();
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'post',
                    data: data,
                    success: function(data){
                        load_queries();
                    }
                });
            });
        })
    </script>
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