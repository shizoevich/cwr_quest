<h1 style="background:#FF7708;color:#ffffff;padding:5px">Some parser(s) are stuck!</h1>
<table style="width:100%">
    <tbody>
    @foreach($data as $queue => $items)
        <tr style="padding:4px;text-align:left">
            <th style="vertical-align:top;background:#ccc;color:#000" width="100">Queue: {{ $queue }} ({{ $items['count'] }})</th>
            <td style="padding:4px;text-align:left;vertical-align:top;background:#eee;color:#000">
                @foreach($items['jobs'] as $job)
                    <div style="background:#ffffff;margin-bottom:5px;padding:5px;">
                        <p style="margin-bottom:0;">
                            <b>Parser:</b>
                            {{ $job['parser'] }}
                        </p>
                        <p style="margin-bottom:0;">
                            <b>Available At:</b>
                            {{ $job['available_at'] ? $job['available_at'] : '-' }}
                        </p>
                        <p style="margin-bottom:0;">
                            <b>Reserved At:</b>
                            {{ $job['reserved_at'] ? $job['reserved_at'] : '-' }}
                        </p>
                    </div>
                @endforeach
            </td>
        </tr>
    @endforeach
    </tbody>
</table>