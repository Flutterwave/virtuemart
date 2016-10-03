<form id="chrgcrd" method="post" class="credit-card">
    <hr>
    <div class="form-header">
        <h4 class="title">Enter Card details</h4>
    </div>

    <div class="form-body">
        <!-- Card Number -->
        <input type="text" class="card-number" name="cardnumber" placeholder="Card Number" value="">

        <!-- Date Field -->
        <div class="date-field">
        <div class="month">
            <select name="cardexpmonth">
            <option value="01">January</option>
            <option value="02">February</option>
            <option value="03">March</option>
            <option value="04">April</option>
            <option value="05">May</option>
            <option value="06">June</option>
            <option value="07">July</option>
            <option value="08">August</option>
            <option value="09">September</option>
            <option value="10">October</option>
            <option value="11">November</option>
            <option value="12">December</option>
            </select>
        </div>
        <div class="year">
            <select name="cardexpyear">
            <option value="16">2016</option>
            <option value="17">2017</option>
            <option value="18">2018</option>
            <option value="19">2019</option>
            <option value="20">2020</option>
            <option value="21">2021</option>
            <option value="22">2022</option>
            <option value="23">2023</option>
            <option value="24">2024</option>
            <option value="25">2025</option>
            </select>
        </div>
        </div>

        <!-- Card Verification Field -->
        <div class="card-verification">
        <div class="cvv-input">
            <input type="text" placeholder="CVV" name="cardcvv" value="">
        </div>
        <div class="cvv-details">
            <p>3 or 4 digits usually found <br> on the signature strip</p>
        </div>
        </div>

        <!-- Buttons -->
        <button id="sub" class="btn btn-default" type="submit">Complete Payment</button>

        <div id="dialog" style="display:none">
            <iframe  width="500" height="500" id="ppb3" style="border:none;z-index:1002;background:#eee;" webkitallowfullscreen="" mozallowfullscreen="" allowfullscreen="">
                <p>Your browser does not support iframes.</p>
            </iframe>
        </div>

        <script type="text/javascript" charset="UTF-8">
            var $ = jQuery;
            $(document).ready(function() {
                var x = false;
                var r = '#';

                $('#chrgcrd').submit(function(event){
                    event.preventDefault();
                    $('#sub').prop("disabled",true);
                    $.post('<?php echo $actionUrl ?>', $('#chrgcrd').serializeArray(), function(result){
                        $('#sub').prop("disabled",false);
                        var data = JSON.parse(result);
                        var content = "Payment failed, please verify payment details.";
                        if(data.status=='ok') {
                            content = (data.resp.data.responsehtml) ?
                                data.resp.data.responsehtml
                                : data.resp.data.responsemessage;
                        }

                        $("#dialog").dialog({
                            position: {my: "center top"},
                            width: 500,
                            height: "auto",
                            modal: true,
                            closeOnEscape: true,
                            position: ['center',70],
                            open: function() {
                                $('.ui-widget-overlay').addClass('custom-overlay');
                            },
                            close: function() {
                                $('.ui-widget-overlay').removeClass('custom-overlay');
                            } 
                        });
                        x = document.getElementById('ppb3').contentWindow.document;
                        x.open();
                        x.write(content);
                        x.close();
                    });
                });

                var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
                var eventer = window[eventMethod];
                var messageEvent = eventMethod == "attachEvent" ? "onmessage" : "message";
                eventer(messageEvent,function(e) {
                    $("#dialog").dialog('close');
                    document.getElementById('ppb3').remove();
                    console.log(e.origin);
                    r = e.data;
                    // if (e.origin != '<?php echo $baseUrl ?>') r = '<?php echo $cancelUrl ?>';
                    window.location = r;
                },false);
            });
        </script>
    </div>
</form>