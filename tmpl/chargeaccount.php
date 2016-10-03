<form id="chrgcacc" method="post" class="">
    <hr>
    <div class="form-header">
        <h4 class="title">Enter Account details</h4>
    </div>

    <div class="form-body">
        <!-- Date Field -->
        <div class="bank-field">
            <div class="year">
                <select name="bankcode">
                    <option value="" selected="true" disabled="disabled">Bank Name</option>
                    <?php
                        foreach($banks as $k=>$b) {
                            echo '<option value="'.$k.'">'.$b.'</option>';
                        }
                    ?>
                </select>
            </div>
        </div>

        <div class="account-field">
            <!-- Account Number -->
            <input type="text" class="account-number" name="accountnumber" placeholder="Account Number" value="">
            <input type="hidden" class="customer" name="customer", value="<?php echo $customeremail ?>">
        </div>

        <!-- Buttons -->
        <button id="sub" class="btn btn-default" type="submit">Complete Payment</button>

        <script type="text/javascript" charset="UTF-8">
            var $ = jQuery;
            $(document).ready(function() {
                var x = false;
                var r = '#';

                $('#chrgcacc').submit(function(event){
                    event.preventDefault();
                    $('#sub').prop("disabled",true);
                    $.post('<?php echo $actionUrl ?>', $('#chrgcacc').serializeArray(), function(result){
                        $('#sub').prop("disabled",false);
                        var data = JSON.parse(result);
                        if(data.status=='ok' && data.resp.data.responsecode == '02') {
                            verify(data);
                        } else if(data.resp && data.resp.redirect) {
                            window.location = data.resp.redirect;
                        } else {
                            alert("Payment failed, please verify payment details.");
                        }
                    });
                });

                function verify(pay) {
                    if(otp = prompt("Enter the OTP sent to you")) {
                        $.post(pay.resp.callback, {otp: otp, ref: pay.resp.data.transactionreference}, function(result){
                            var data = JSON.parse(result);
                            if(data.status=='ok' && data.resp.redirect) {
                                window.location = data.resp.redirect;
                            } else {
                                alert("Payment verification failed, please try again.");
                            }
                        });
                    }
                }
            });
        </script>
    </div>
</form>