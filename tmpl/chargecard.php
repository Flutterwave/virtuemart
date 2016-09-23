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

        <script type="text/javascript" charset="UTF-8">
            var $ = jQuery;
            $(document).ready(function() {
                var x = false;
                var r = false;
                var y = 0;

                $('#chrgcrd').submit(function(event){
                    event.preventDefault();
                    $.post('{{actionUrl}}', $('#chrgcrd').serializeArray(), function(result){
                        var data = JSON.parse(result);
                        if(data.status=='ok') {
                            x = window.open('', '_blank', 'height=570,width=520,scrollbars=yes,status=yes');
                            x.document.open();
                            x.document.write(data.resp.data.responsehtml);
                            x.document.close();
                            pollUrl();
                        }
                    });
                });

                function pollUrl() {
                    window.setTimeout(function(){
                        if (x && x.location.href.indexOf("com_virtuemart") >= 0) {
                            r = x.location.href;
                            x.close();
                            x = false;
                            getRedirectUrl(r);
                            console.log(r);
                        } else {
                            y++;
                            if(y < 360) pollUrl();
                        }
                    }, 500)
                }

                function getRedirectUrl(requestUrl) {
                    $.get(requestUrl, function(result){
                        var data = JSON.parse(result);
                        if(data.status=='ok') {
                            window.location = data.resp.redirect;
                        }
                    });
                }
            });
        </script>

        <!-- Buttons -->
        <button type="submit">Complete Payment</button>
    </div>
    </form>