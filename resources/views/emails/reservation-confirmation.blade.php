<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Confirmation - Hotel De SLSU</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .content {
            padding: 30px;
        }
        .credentials-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .credential-item {
            background: white;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 10px;
        }
        .credential-label {
            font-size: 12px;
            color: #856404;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .credential-value {
            font-size: 18px;
            color: #1e293b;
            font-weight: 700;
            word-break: break-all;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .info-table th,
        .info-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        .info-table th {
            color: #64748b;
            font-weight: 500;
        }
        .info-table td {
            color: #1e293b;
            font-weight: 600;
        }
        .alert {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .alert strong {
            color: #991b1b;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 10px 5px;
        }
        .footer {
            background: #f8fafc;
            padding: 20px;
            text-align: center;
            color: #64748b;
            font-size: 14px;
        }
        ul {
            line-height: 1.8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Reservation Confirmed!</h1>
            <p style="margin: 10px 0 0 0; opacity: 0.95;">Hotel De SLSU</p>
        </div>

        <div class="content">
            <p>Dear {{ $first_name }} {{ $last_name }},</p>

            <p>Thank you for choosing Hotel De SLSU! Your reservation has been confirmed.</p>

            <h2 style="color: #1e293b; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">Reservation Details</h2>

            <table class="info-table">
                <tr>
                    <th>Reservation ID:</th>
                    <td>#{{ $reservation_id }}</td>
                </tr>
                <tr>
                    <th>Room Type:</th>
                    <td>{{ $room_type_name }}</td>
                </tr>
                <tr>
                    <th>Room Number:</th>
                    <td>{{ $room_number }}</td>
                </tr>
                <tr>
                    <th>Check-in:</th>
                    <td>{{ date('F d, Y', strtotime($arrival_date)) }}</td>
                </tr>
                <tr>
                    <th>Check-out:</th>
                    <td>{{ date('F d, Y', strtotime($departure_date)) }}</td>
                </tr>
                <tr>
                    <th>Number of Nights:</th>
                    <td>{{ $no_nights }} night{{ $no_nights > 1 ? 's' : '' }}</td>
                </tr>
                <tr>
                    <th>Total Amount:</th>
                    <td>₱{{ number_format($total_amount, 2) }}</td>
                </tr>
                <tr>
                    <th>Remaining Balance:</th>
                    <td style="color: #f59e0b;">₱{{ number_format($balance, 2) }}</td>
                </tr>
            </table>

            <div class="credentials-box">
                <h3 style="color: #856404; margin-top: 0;">Your Account Credentials</h3>
                <p style="color: #856404;">Use these credentials to log in and manage your reservation:</p>

                <div class="credential-item">
                    <div class="credential-label">Email / Username</div>
                    <div class="credential-value">{{ $email }}</div>
                </div>

                <div class="credential-item">
                    <div class="credential-label">Temporary Password</div>
                    <div class="credential-value">{{ $password }}</div>
                </div>
            </div>

            <div class="alert">
                <strong>⚠️ Important:</strong> Please change your password after your first login for security purposes.
            </div>

            <h3 style="color: #1e293b;">Important Information</h3>
            <ul style="color: #64748b;">
                <li>Check-in time: 2:00 PM</li>
                <li>Check-out time: 12:00 PM</li>
                <li>Please bring a valid ID upon check-in</li>
                <li>Remaining balance of ₱{{ number_format($balance, 2) }} must be paid during your stay</li>
                <li>Cancellation must be made at least 24 hours before check-in</li>
            </ul>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ config('app.url') }}/login" class="button">Login to Your Account</a>
            </div>

            <p>If you have any questions, please don't hesitate to contact us.</p>

            <p>We look forward to welcoming you!</p>

            <p style="margin-top: 30px;">
                Best regards,<br>
                <strong>Hotel De SLSU Team</strong>
            </p>
        </div>

        <div class="footer">
            <p>This is an automated email. Please do not reply to this message.</p>
            <p>&copy; {{ date('Y') }} Hotel De SLSU. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
