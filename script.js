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
});