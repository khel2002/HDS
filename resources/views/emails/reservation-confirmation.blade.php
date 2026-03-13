<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reservation Confirmation</title>
</head>
<body style="margin:0; padding:0; background:#f1f5f9; font-family:'Segoe UI',Arial,sans-serif; color:#1e293b;">

  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9; padding:32px 0;">
    <tr>
      <td align="center">
        <table width="620" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,0.08); max-width:620px; width:100%;">

          {{-- HEADER --}}
          <tr>
            <td style="background:linear-gradient(135deg,#060E4D,#013a72); padding:40px 40px 32px; text-align:center;">
              <div style="font-size:48px; margin-bottom:12px;">🏨</div>
              <h1 style="margin:0; color:#ffffff; font-size:26px; font-weight:700; letter-spacing:-0.5px;">
                Hotel De SLSU
              </h1>
              <p style="margin:6px 0 0; color:rgba(255,255,255,0.75); font-size:14px;">
                Reservation Confirmation
              </p>
              <div style="display:inline-block; background:#10b981; color:#fff; font-size:13px; font-weight:700; padding:6px 20px; border-radius:20px; margin-top:16px;">
                ✓ Booking Confirmed
              </div>
            </td>
          </tr>

          {{-- GREETING --}}
          <tr>
            <td style="padding:32px 40px 0;">
              <p style="margin:0 0 8px; font-size:17px;">
                Hello, <strong style="color:#060E4D;">{{ $first_name }} {{ $last_name }}</strong>!
              </p>
              <p style="margin:0; color:#64748b; font-size:14px; line-height:1.7;">
                Your reservation at Hotel De SLSU has been successfully created.
                Below are your booking details and temporary account credentials.
              </p>
            </td>
          </tr>

          {{-- RESERVATION DETAILS --}}
          <tr>
            <td style="padding:28px 40px 0;">
              <p style="margin:0 0 12px; font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.08em;">
                Reservation Details
              </p>
              <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; overflow:hidden;">
                <tr>
                  <td style="padding:14px 20px; border-bottom:1px solid #e2e8f0; font-size:13px; color:#64748b; font-weight:500; width:40%;">Room(s)</td>
                  <td style="padding:14px 20px; border-bottom:1px solid #e2e8f0; font-size:13px; color:#1e293b; font-weight:600; text-align:right;">
                    @if(!empty($rooms) && count($rooms) > 0)
                      @foreach($rooms as $r)
                        {{ $r['room_type_name'] }} — Rm {{ $r['room_number'] }}<br>
                      @endforeach
                    @else
                      —
                    @endif
                  </td>
                </tr>
                <tr>
                  <td style="padding:14px 20px; border-bottom:1px solid #e2e8f0; font-size:13px; color:#64748b; font-weight:500;">Check-in</td>
                  <td style="padding:14px 20px; border-bottom:1px solid #e2e8f0; font-size:13px; color:#1e293b; font-weight:600; text-align:right;">
                    {{ date('F d, Y', strtotime($arrival_date)) }}
                  </td>
                </tr>
                <tr>
                  <td style="padding:14px 20px; border-bottom:1px solid #e2e8f0; font-size:13px; color:#64748b; font-weight:500;">Check-out</td>
                  <td style="padding:14px 20px; border-bottom:1px solid #e2e8f0; font-size:13px; color:#1e293b; font-weight:600; text-align:right;">
                    {{ date('F d, Y', strtotime($departure_date)) }}
                  </td>
                </tr>
                <tr>
                  <td style="padding:14px 20px; border-bottom:1px solid #e2e8f0; font-size:13px; color:#64748b; font-weight:500;">Number of Nights</td>
                  <td style="padding:14px 20px; border-bottom:1px solid #e2e8f0; font-size:13px; color:#1e293b; font-weight:600; text-align:right;">
                    {{ $no_nights }} night{{ $no_nights > 1 ? 's' : '' }}
                  </td>
                </tr>
                <tr>
                  <td style="padding:14px 20px; font-size:13px; color:#64748b; font-weight:500;">Number of Rooms</td>
                  <td style="padding:14px 20px; font-size:13px; color:#1e293b; font-weight:600; text-align:right;">
                    {{ $room_count }} room{{ $room_count > 1 ? 's' : '' }}
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          {{-- PAYMENT SUMMARY --}}
          <tr>
            <td style="padding:24px 40px 0;">
              <p style="margin:0 0 12px; font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.08em;">
                Payment Summary
              </p>
              <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; overflow:hidden;">
                <tr>
                  <td style="padding:14px 20px; border-bottom:1px solid #e2e8f0; font-size:13px; color:#64748b; font-weight:500;">Total Amount</td>
                  <td style="padding:14px 20px; border-bottom:1px solid #e2e8f0; font-size:13px; color:#1e293b; font-weight:700; text-align:right;">
                    ₱{{ number_format($total_amount, 2) }}
                  </td>
                </tr>
                <tr>
                  <td style="padding:14px 20px; border-bottom:1px solid #e2e8f0; font-size:13px; color:#64748b; font-weight:500;">Reservation Fee (Pay Now)</td>
                  <td style="padding:14px 20px; border-bottom:1px solid #e2e8f0; font-size:13px; color:#10b981; font-weight:700; text-align:right;">
                    ₱{{ number_format($reservation_fee, 2) }}
                  </td>
                </tr>
                <tr>
                  <td style="padding:14px 20px; font-size:13px; color:#64748b; font-weight:500;">Remaining Balance</td>
                  <td style="padding:14px 20px; font-size:13px; color:#f59e0b; font-weight:700; text-align:right;">
                    ₱{{ number_format($balance, 2) }}
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          {{-- TEMPORARY CREDENTIALS --}}
          <tr>
            <td style="padding:24px 40px 0;">
              <p style="margin:0 0 12px; font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.08em;">
                Your Temporary Account
              </p>
              <table width="100%" cellpadding="0" cellspacing="0" style="background:#fffbeb; border:2px solid #fbbf24; border-radius:10px; overflow:hidden;">
                <tr>
                  <td style="padding:16px 20px; border-bottom:1px solid #fde68a;">
                    <p style="margin:0 0 4px; font-size:11px; font-weight:700; color:#92400e; text-transform:uppercase; letter-spacing:0.06em;">Email / Username</p>
                    <p style="margin:0; font-size:15px; font-weight:700; color:#1e293b;">{{ $email ?? '' }}</p>
                  </td>
                </tr>
                <tr>
                  <td style="padding:16px 20px;">
                    <p style="margin:0 0 4px; font-size:11px; font-weight:700; color:#92400e; text-transform:uppercase; letter-spacing:0.06em;">Temporary Password</p>
                    <p style="margin:0; font-size:15px; font-weight:700; color:#1e293b; word-break:break-all; font-family:monospace;">{{ $password ?? '' }}</p>
                  </td>
                </tr>
              </table>
              <p style="margin:10px 0 0; font-size:12px; color:#ef4444; font-weight:600;">
                ⚠ Please change your password immediately after your first login.
              </p>
            </td>
          </tr>

          {{-- IMPORTANT NOTES --}}
          <tr>
            <td style="padding:24px 40px 0;">
              <p style="margin:0 0 12px; font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.08em;">
                Important Information
              </p>
              <table width="100%" cellpadding="0" cellspacing="0" style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px;">
                <tr>
                  <td style="padding:16px 20px;">
                    <p style="margin:0; font-size:13px; color:#166534; line-height:2.2;">
                      🕑 <strong>Check-in time:</strong> 2:00 PM<br>
                      🕛 <strong>Check-out time:</strong> 12:00 PM<br>
                      🪪 <strong>Bring a valid ID</strong> upon check-in<br>
                      💵 <strong>Reservation fee of ₱{{ number_format($reservation_fee, 2) }}</strong> must be paid upon check-in<br>
                      💳 <strong>Remaining balance of ₱{{ number_format($balance, 2) }}</strong> must be paid during your stay<br>
                      ❌ <strong>Cancellations</strong> must be made at least 24 hours before check-in
                    </p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          {{-- CTA BUTTON --}}
          <tr>
            <td style="padding:32px 40px;" align="center">
              <a href="{{ route('login') }}"
                 style="display:inline-block; background:linear-gradient(135deg,#060E4D,#013a72); color:#ffffff;
                        font-size:15px; font-weight:700; padding:14px 40px; border-radius:8px;
                        text-decoration:none; letter-spacing:0.02em;">
                Login to Your Account →
              </a>
            </td>
          </tr>

          {{-- FOOTER --}}
          <tr>
            <td style="background:#f8fafc; border-top:1px solid #e2e8f0; padding:24px 40px; text-align:center;">
              <p style="margin:0 0 4px; font-size:13px; font-weight:600; color:#475569;">Hotel De SLSU</p>
              <p style="margin:0; font-size:12px; color:#94a3b8; line-height:1.6;">
                This is an automated confirmation email. Please do not reply to this message.<br>
                If you have questions, contact us directly at the hotel.
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>