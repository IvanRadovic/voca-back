<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; }
        .frame {
            border: 10px solid #0284c7;
            margin: 18px;
            padding: 50px 60px;
            text-align: center;
            height: 480px;
            position: relative;
        }
        .inner { border: 2px solid #bae6fd; padding: 40px 30px; height: 100%; }
        .brand { color: #0284c7; font-size: 26px; font-weight: bold; letter-spacing: 2px; }
        .subtitle { color: #64748b; font-size: 13px; letter-spacing: 4px; text-transform: uppercase; margin-top: 6px; }
        .title { font-size: 34px; font-weight: bold; margin-top: 40px; }
        .label { color: #64748b; font-size: 13px; margin-top: 26px; }
        .name { font-size: 30px; font-weight: bold; color: #0369a1; margin-top: 6px; }
        .event { font-size: 18px; margin-top: 22px; }
        .org { color: #475569; font-size: 14px; margin-top: 4px; }
        .footer { position: absolute; bottom: 26px; left: 60px; right: 60px; font-size: 11px; color: #94a3b8; }
        .footer table { width: 100%; }
        .footer td { font-size: 11px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="frame">
        <div class="inner">
            <div class="brand">VOCA</div>
            <div class="subtitle">Opportunities for young people</div>

            <div class="title">Certificate of Participation</div>

            <div class="label">This certifies that</div>
            <div class="name">{{ $recipient }}</div>

            <div class="event">successfully participated in <strong>{{ $title }}</strong></div>
            <div class="org">organized by {{ $organization }}</div>

            <div class="footer">
                <table>
                    <tr>
                        <td style="text-align:left;">Issued: {{ $date }}</td>
                        <td style="text-align:center;">Verify at {{ $verifyUrl }}</td>
                        <td style="text-align:right;">Code: {{ $code }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
