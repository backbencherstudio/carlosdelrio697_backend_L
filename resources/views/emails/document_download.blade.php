<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f7f9; margin: 0; padding: 0; -webkit-font-smoothing: antialiased; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f4f7f9; padding-bottom: 40px; }
        .main { background-color: #ffffff; margin: 0 auto; width: 100%; max-width: 600px; border-spacing: 0; color: #4a4a4a; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .header { background-color: #4da04d; padding: 30px; text-align: center; color: #ffffff; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 600; letter-spacing: 1px; }
        .content { padding: 40px 30px; line-height: 1.6; }
        .content h2 { color: #2d4844; margin-top: 0; font-size: 20px; }
        .order-card { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 20px; margin: 25px 0; }
        .order-table { width: 100%; border-collapse: collapse; }
        .order-table td { padding: 8px 0; font-size: 14px; }
        .label { color: #718096; font-weight: 500; width: 40%; }
        .value { color: #2d3748; font-weight: 600; text-align: right; }
        .btn-container { text-align: center; margin-top: 30px; }
        .btn { background-color: #3182ce; color: #ffffff !important; padding: 14px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px; display: inline-block; transition: background-color 0.3s; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #a0aec0; }
        .badge { background-color: #c6f6d5; color: #22543d; padding: 2px 8px; border-radius: 999px; font-size: 11px; text-transform: uppercase; }
        hr { border: 0; border-top: 1px solid #e2e8f0; margin: 30px 0; }
    </style>
</head>
<body>
    <center class="wrapper">
        <table class="main" width="100%">
            <!-- Header -->
            <tr>
                <td class="header">
                    <h1>{{ config('app.name') }}</h1>
                </td>
            </tr>

            <!-- Content -->
            <tr>
                <td class="content">
                    <h2>Payment Successful!</h2>
                    <p>Hello <strong>{{ $order->customer_name }}</strong>,</p>
                    <p>Thank you for choosing our service. Your payment has been confirmed, and your document is now ready for download.</p>

                    <!-- Order Summary Card -->
                    <div class="order-card">
                        <table class="order-table">
                            <tr>
                                <td class="label">Order Number</td>
                                <td class="value">#{{ $order->order_number }}</td>
                            </tr>
                            <tr>
                                <td class="label">Service</td>
                                <td class="value">{{ $order->service_name }}</td>
                            </tr>
                            <tr>
                                <td class="label">State</td>
                                <td class="value">{{ $order->state }}</td>
                            </tr>
                            <tr>
                                <td class="label">Amount Paid</td>
                                <td class="value">${{ number_format($order->amount, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="label">Transaction ID:</td>
                                <td class="value">{{ $order->stripe_transaction_id }}</td>
                            </tr>
                            <tr>
                                <td class="label">Status</td>
                                <td class="value"><span class="badge">Completed</span></td>
                            </tr>
                        </table>
                    </div>

                    <!-- CTA Button -->
                    <div class="btn-container">
                        <a href="{{ $downloadUrl }}" class="btn">Download Your Document</a>
                    </div>

                </td>
            </tr>
        </table>
    </center>
</body>
</html>
