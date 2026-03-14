<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>{{ $proposal->title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9.5pt;
            color: #0D0F14;
            background: #fff;
            line-height: 1.65;
        }

        table {
            border-collapse: collapse;
        }

        p {
            margin-bottom: 7px;
        }

        ul,
        ol {
            padding-left: 16px;
            margin-bottom: 7px;
        }

        li {
            margin-bottom: 2px;
        }

        .section {
            margin-bottom: 22px;
        }

        .section-title {
            font-size: 12.5pt;
            font-weight: 700;
            color: #0D0F14;
            padding-bottom: 8px;
            border-bottom: 2px solid #EDF0F7;
            margin-bottom: 10px;
        }

        .muted {
            color: #8B95A6;
        }

        .accent {
            color: #1A56F0;
        }
    </style>
</head>

<body>

    {{-- ── COVER HEADER ─────────────────────────────── --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#0D1628;margin-bottom:0;">
        <tr>
            <td style="height:5px;background:#1A56F0;font-size:0;">&nbsp;</td>
        </tr>
        <tr>
            <td style="padding:36px 44px 32px;">

                <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:40px;">
                    <tr>
                        <td width="38" valign="middle">
                            <table cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="width:36px;height:36px;background:#1A56F0;color:#fff;font-size:14pt;font-weight:700;text-align:center;line-height:36px;">{{ strtoupper(substr($proposal->sender->name??'P',0,1)) }}</td>
                                </tr>
                            </table>
                        </td>
                        <td valign="middle" style="padding-left:10px;">
                            <div style="font-size:11pt;font-weight:700;color:#fff;">{{ $proposal->sender->name??'ProposalCraft' }}</div>
                            <div style="font-size:7.5pt;color:rgba(255,255,255,0.35);">{{ $proposal->sender->email??'' }}</div>
                        </td>
                    </tr>
                </table>

                <div style="font-size:6.5pt;font-weight:700;color:#4D78F5;text-transform:uppercase;letter-spacing:0.12em;margin-bottom:12px;">&#10022; &nbsp;PROPOSAL DOCUMENT</div>
                <div style="font-size:22pt;font-weight:700;color:#fff;line-height:1.1;letter-spacing:-0.02em;margin-bottom:7px;">{{ $proposal->title }}</div>
                <div style="font-size:9.5pt;color:rgba(255,255,255,0.38);margin-bottom:28px;">Prepared exclusively for {{ $proposal->client }}</div>

                <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:20px;">
                    <tr>
                        <td width="25%" valign="top" style="padding-right:10px;">
                            <div style="font-size:6pt;font-weight:700;color:rgba(255,255,255,0.28);text-transform:uppercase;letter-spacing:0.1em;margin-bottom:3px;">Prepared for</div>
                            <div style="font-size:9pt;font-weight:700;color:#fff;">{{ $proposal->client }}</div>
                        </td>
                        <td width="25%" valign="top" style="padding-right:10px;">
                            <div style="font-size:6pt;font-weight:700;color:rgba(255,255,255,0.28);text-transform:uppercase;letter-spacing:0.1em;margin-bottom:3px;">Prepared by</div>
                            <div style="font-size:9pt;font-weight:700;color:#fff;">{{ $proposal->sender->name??'—' }}</div>
                        </td>
                        <td width="25%" valign="top" style="padding-right:10px;">
                            <div style="font-size:6pt;font-weight:700;color:rgba(255,255,255,0.28);text-transform:uppercase;letter-spacing:0.1em;margin-bottom:3px;">Date</div>
                            <div style="font-size:9pt;font-weight:700;color:#fff;">{{ $proposal->created_at->format('M j, Y') }}</div>
                        </td>
                        <td width="25%" valign="top">
                            <div style="font-size:6pt;font-weight:700;color:rgba(255,255,255,0.28);text-transform:uppercase;letter-spacing:0.1em;margin-bottom:3px;">Valid Until</div>
                            <div style="font-size:9pt;font-weight:700;color:#fff;">{{ $proposal->expires_at?$proposal->expires_at->format('M j, Y'):'30 days' }}</div>
                        </td>
                    </tr>
                </table>

                @php
                $sm=['pending'=>['#1C2D5E','#E8A838','Awaiting Review'],'viewed'=>['#162454','#4D78F5','Under Review'],'accepted'=>['#0A2E1E','#0DBD7F','Accepted'],'declined'=>['#2E0A12','#F04060','Declined']];
                $s=$sm[$proposal->status]??$sm['pending'];
                @endphp
                <table width="100%" cellpadding="0" cellspacing="0" style="background:{{ $s[0] }};">
                    <tr>
                        <td style="padding:14px 18px;" valign="middle">
                            <div style="font-size:6pt;color:rgba(255,255,255,0.3);text-transform:uppercase;letter-spacing:0.1em;margin-bottom:3px;">Total Investment</div>
                            <div style="font-size:20pt;font-weight:700;color:#fff;letter-spacing:-0.02em;">${{ number_format($proposal->amount??0,2) }}</div>
                        </td>
                        <td style="padding:14px 18px;" valign="middle" align="right">
                            <div style="font-size:7pt;font-weight:700;color:{{ $s[1] }};text-transform:uppercase;letter-spacing:0.06em;">{{ $s[2] }}</div>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>

    {{-- ── INTRO ────────────────────────────────────── --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:0;">
        <tr>
            <td style="padding:28px 44px 0;">

                <div style="font-size:6.5pt;font-weight:700;color:#1A56F0;text-transform:uppercase;letter-spacing:0.12em;margin-bottom:7px;">Cover Letter</div>
                <div style="font-size:15pt;font-weight:700;color:#0D0F14;letter-spacing:-0.02em;margin-bottom:14px;">Dear {{ $proposal->client }},</div>

                <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:16px;">
                    <tr>
                        <td width="3" style="background:#1A56F0;font-size:0;">&nbsp;</td>
                        <td style="background:#F4F6FF;padding:13px 17px;font-size:9.5pt;color:#2D3748;line-height:1.85;">{{ $proposal->intro_message??'Thank you for the opportunity to present this proposal. We have carefully prepared this document to outline our approach, timeline, and investment for your project. We are confident in our ability to deliver exceptional results and look forward to the opportunity of working together.' }}</td>
                    </tr>
                </table>

                <table cellpadding="0" cellspacing="0">
                    <tr>
                        <td width="38" valign="top">
                            <table cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="width:34px;height:34px;background:#1A56F0;color:#fff;font-size:13pt;font-weight:700;text-align:center;line-height:34px;">{{ strtoupper(substr($proposal->sender->name??'P',0,1)) }}</td>
                                </tr>
                            </table>
                        </td>
                        <td valign="top" style="padding-left:9px;">
                            <div style="font-size:9.5pt;font-weight:700;color:#0D0F14;">{{ $proposal->sender->name??'The Team' }}</div>
                            <div style="font-size:7.5pt;color:#8B95A6;">{{ $proposal->sender->email??'' }}</div>
                            <div style="font-size:7.5pt;font-weight:700;color:#1A56F0;">ProposalCraft</div>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>

    {{-- ── SECTIONS ─────────────────────────────────── --}}
    @if($proposal->sections && $proposal->sections->count())
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding:20px 44px 0;">

                <table width="100%" cellpadding="0" cellspacing="0" style="border-top:2px solid #EDF0F7;margin-bottom:18px;margin-top:18px;">
                    <tr>
                        <td style="font-size:6.5pt;font-weight:700;color:#8B95A6;text-transform:uppercase;letter-spacing:0.12em;padding-top:8px;">Proposal Details</td>
                    </tr>
                </table>

                @foreach($proposal->sections->sortBy('order') as $idx => $section)
                <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:18px;">
                    <tr>
                        <td style="padding-bottom:7px;border-bottom:1px solid #EDF0F7;">
                            <table cellpadding="0" cellspacing="0">
                                <tr>
                                    <td width="26" valign="middle">
                                        <table cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="width:20px;height:20px;background:#1A56F0;color:#fff;font-size:7pt;font-weight:700;text-align:center;line-height:20px;">{{ $idx+1 }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td valign="middle" style="padding-left:7px;font-size:11.5pt;font-weight:700;color:#0D0F14;letter-spacing:-0.015em;">{{ $section->title }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top:9px;">

                            @if($section->type==='text')
                            <div style="font-size:9.5pt;color:#374151;line-height:1.85;">{!! $section->content !!}</div>

                            @elseif($section->type==='services')
                            @php $items=is_string($section->content)?json_decode($section->content,true):$section->content; @endphp
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:7px;">
                                <thead>
                                    <tr style="background:#0D0F14;">
                                        <th style="padding:7px 10px;font-size:6.5pt;font-weight:700;color:#fff;text-transform:uppercase;text-align:left;width:36%;">Service</th>
                                        <th style="padding:7px 10px;font-size:6.5pt;font-weight:700;color:#fff;text-transform:uppercase;text-align:left;width:34%;">Description</th>
                                        <th style="padding:7px 10px;font-size:6.5pt;font-weight:700;color:#fff;text-transform:uppercase;text-align:center;width:10%;">Qty</th>
                                        <th style="padding:7px 10px;font-size:6.5pt;font-weight:700;color:#fff;text-transform:uppercase;text-align:right;width:20%;">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(is_array($items))
                                    @foreach($items as $i=>$item)
                                    <tr style="background:{{ $i%2===0?'#fff':'#F8F9FC' }};">
                                        <td style="padding:8px 10px;border-bottom:1px solid #EDF0F7;font-size:8.5pt;font-weight:600;color:#0D0F14;">{{ $item['name']??'—' }}</td>
                                        <td style="padding:8px 10px;border-bottom:1px solid #EDF0F7;font-size:7.5pt;color:#8B95A6;">{{ $item['description']??'' }}</td>
                                        <td style="padding:8px 10px;border-bottom:1px solid #EDF0F7;font-size:8.5pt;color:#374151;text-align:center;">{{ $item['quantity']??1 }}</td>
                                        <td style="padding:8px 10px;border-bottom:1px solid #EDF0F7;font-size:8.5pt;font-weight:600;color:#0D0F14;text-align:right;">${{ number_format($item['price']??0,2) }}</td>
                                    </tr>
                                    @endforeach
                                    @endif
                                </tbody>
                            </table>
                            <table width="190" align="right" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding:4px 0;font-size:7.5pt;color:#8B95A6;border-bottom:1px solid #EDF0F7;">Subtotal</td>
                                    <td align="right" style="padding:4px 0;font-size:7.5pt;font-weight:600;color:#0D0F14;border-bottom:1px solid #EDF0F7;">${{ number_format($proposal->amount??0,2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="padding-top:4px;">
                                        <table width="100%" cellpadding="0" cellspacing="0" style="background:#0D0F14;">
                                            <tr>
                                                <td style="padding:6px 10px;font-size:7pt;font-weight:700;color:rgba(255,255,255,0.5);text-transform:uppercase;">Total</td>
                                                <td align="right" style="padding:6px 10px;font-size:11pt;font-weight:700;color:#fff;">${{ number_format($proposal->amount??0,2) }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            @elseif($section->type==='timeline')
                            @php $ms=is_string($section->content)?json_decode($section->content,true):$section->content; @endphp
                            @if(is_array($ms))
                            @foreach($ms as $i=>$m)
                            <table cellpadding="0" cellspacing="0" style="margin-bottom:10px;width:100%;">
                                <tr>
                                    <td width="28" valign="top">
                                        <table cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="width:22px;height:22px;background:#1A56F0;color:#fff;font-size:7pt;font-weight:700;text-align:center;line-height:22px;">{{ $i+1 }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td valign="top" style="padding-left:9px;">
                                        <div style="font-size:9.5pt;font-weight:700;color:#0D0F14;margin-bottom:2px;">{{ $m['title']??'Milestone '.($i+1) }}</div>
                                        <div style="font-size:8pt;color:#6B7280;line-height:1.6;">{{ $m['description']??'' }}</div>
                                        @if(!empty($m['duration']))<div style="font-size:7pt;font-weight:700;color:#1A56F0;margin-top:2px;">{{ $m['duration'] }}</div>@endif
                                    </td>
                                </tr>
                            </table>
                            @endforeach
                            @endif

                            @elseif($section->type==='terms')
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td width="3" style="background:#E8A838;font-size:0;">&nbsp;</td>
                                    <td style="background:#FFFBF0;padding:11px 15px;font-size:8.5pt;color:#5A4A2A;line-height:1.75;">{!! $section->content !!}</td>
                                </tr>
                            </table>

                            @else
                            <div style="font-size:9.5pt;color:#374151;line-height:1.85;">{!! $section->content !!}</div>
                            @endif

                        </td>
                    </tr>
                </table>
                @endforeach

            </td>
        </tr>
    </table>
    @endif

    {{-- ── SIGNATURE ────────────────────────────────── --}}
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding:20px 44px 36px;">

                <table width="100%" cellpadding="0" cellspacing="0" style="border-top:2px solid #EDF0F7;margin-bottom:16px;margin-top:10px;">
                    <tr>
                        <td style="padding-top:8px;font-size:14pt;font-weight:700;color:#0D0F14;letter-spacing:-0.02em;">Acceptance &amp; Signature</td>
                    </tr>
                    <tr>
                        <td style="font-size:8.5pt;color:#8B95A6;padding-bottom:12px;">By signing below, both parties agree to the terms outlined in this proposal.</td>
                    </tr>
                </table>

                @if($proposal->status==='accepted' && $proposal->accepted_by)
                <table width="100%" cellpadding="0" cellspacing="0" style="background:#0A2E1E;margin-bottom:14px;">
                    <tr>
                        <td width="32" valign="top" style="padding:11px 0 11px 12px;font-size:12pt;color:#0DBD7F;">&#10003;</td>
                        <td valign="top" style="padding:11px 12px 11px 7px;">
                            <div style="font-size:10pt;font-weight:700;color:#0DBD7F;margin-bottom:2px;">Proposal Accepted</div>
                            <div style="font-size:7.5pt;color:#6ECFAA;">Accepted by <strong>{{ $proposal->accepted_by }}</strong>@if($proposal->accepted_email) ({{ $proposal->accepted_email }})@endif on {{ $proposal->accepted_at?->format('F j, Y \a\t g:i A')??'—' }}</div>
                        </td>
                    </tr>
                </table>
                @endif

                <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:18px;">
                    <tr>
                        <td width="48%" valign="top" style="padding-right:10px;">
                            <div style="font-size:6.5pt;font-weight:700;color:#8B95A6;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:4px;">Client Signature</div>
                            <table width="100%" cellpadding="0" cellspacing="0" style="border:1.5px solid #DDE1EE;background:#F8F9FC;">
                                <tr>
                                    <td style="padding:12px;">
                                        @if($proposal->status==='accepted')
                                        <div style="font-size:10pt;font-weight:700;color:#0D0F14;">{{ $proposal->accepted_by }}</div>
                                        <div style="font-size:7.5pt;color:#8B95A6;margin-top:2px;">{{ $proposal->accepted_email }}</div>
                                        @if($proposal->signature_path)<img src="{{ storage_path('app/public/'.$proposal->signature_path) }}" style="max-width:150px;max-height:44px;margin-top:6px;" />@endif
                                        <div style="margin-top:9px;padding-top:6px;border-top:1px solid #DDE1EE;font-size:7pt;color:#8B95A6;">Signed {{ $proposal->accepted_at?->format('M j, Y') }}</div>
                                        @else
                                        <div style="height:32px;">&nbsp;</div>
                                        <div style="padding-top:6px;border-top:1px solid #DDE1EE;font-size:7pt;color:#C0C8D8;">Client signature</div>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td width="4%">&nbsp;</td>
                        <td width="48%" valign="top">
                            <div style="font-size:6.5pt;font-weight:700;color:#8B95A6;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:4px;">Authorized by</div>
                            <table width="100%" cellpadding="0" cellspacing="0" style="border:1.5px solid #DDE1EE;background:#F8F9FC;">
                                <tr>
                                    <td style="padding:12px;">
                                        <div style="font-size:10pt;font-weight:700;color:#0D0F14;">{{ $proposal->sender->name??'—' }}</div>
                                        <div style="font-size:7.5pt;color:#8B95A6;margin-top:2px;">{{ $proposal->sender->email??'' }}</div>
                                        <div style="height:32px;">&nbsp;</div>
                                        <div style="padding-top:6px;border-top:1px solid #DDE1EE;font-size:7pt;color:#8B95A6;">Signed {{ $proposal->created_at->format('M j, Y') }}</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:18px;">
                    <thead>
                        <tr style="background:#0D0F14;">
                            <th colspan="2" style="padding:7px 10px;font-size:6.5pt;font-weight:700;color:#fff;text-transform:uppercase;letter-spacing:0.07em;text-align:left;">Proposal Summary</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="background:#fff;">
                            <td style="padding:7px 10px;font-size:8pt;color:#8B95A6;border-bottom:1px solid #EDF0F7;width:35%;">Proposal Title</td>
                            <td style="padding:7px 10px;font-size:8pt;color:#0D0F14;border-bottom:1px solid #EDF0F7;">{{ $proposal->title }}</td>
                        </tr>
                        <tr style="background:#F8F9FC;">
                            <td style="padding:7px 10px;font-size:8pt;color:#8B95A6;border-bottom:1px solid #EDF0F7;">Client</td>
                            <td style="padding:7px 10px;font-size:8pt;color:#0D0F14;border-bottom:1px solid #EDF0F7;">{{ $proposal->client }}</td>
                        </tr>
                        <tr style="background:#fff;">
                            <td style="padding:7px 10px;font-size:8pt;color:#8B95A6;border-bottom:1px solid #EDF0F7;">Prepared by</td>
                            <td style="padding:7px 10px;font-size:8pt;color:#0D0F14;border-bottom:1px solid #EDF0F7;">{{ $proposal->sender->name??'—' }}</td>
                        </tr>
                        <tr style="background:#F8F9FC;">
                            <td style="padding:7px 10px;font-size:8pt;color:#8B95A6;border-bottom:1px solid #EDF0F7;">Date Created</td>
                            <td style="padding:7px 10px;font-size:8pt;color:#0D0F14;border-bottom:1px solid #EDF0F7;">{{ $proposal->created_at->format('F j, Y') }}</td>
                        </tr>
                        <tr style="background:#fff;">
                            <td style="padding:7px 10px;font-size:8pt;color:#8B95A6;border-bottom:1px solid #EDF0F7;">Total Amount</td>
                            <td style="padding:7px 10px;font-size:8.5pt;font-weight:700;color:#1A56F0;border-bottom:1px solid #EDF0F7;">${{ number_format($proposal->amount??0,2) }}</td>
                        </tr>
                        <tr style="background:#F8F9FC;">
                            <td style="padding:7px 10px;font-size:8pt;color:#8B95A6;">Status</td>
                            <td style="padding:7px 10px;font-size:8pt;font-weight:700;color:#0D0F14;">{{ ucfirst($proposal->status) }}</td>
                        </tr>
                    </tbody>
                </table>

                <table width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid #EDF0F7;">
                    <tr>
                        <td style="font-size:6.5pt;color:#C0C8D8;text-align:center;padding-top:10px;">
                            Generated by <span style="color:#1A56F0;font-weight:700;">ProposalCraft</span>
                            &nbsp;&middot;&nbsp; {{ now()->format('F j, Y') }}
                            &nbsp;&middot;&nbsp; Confidential Document
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>

</body>

</html>