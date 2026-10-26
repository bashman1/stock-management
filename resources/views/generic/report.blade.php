<!doctype html>
<html lang="en">

<head>
    <style>
        @page {
            margin: 100px 25px;
        }

        body {
            margin: 0;
            padding: 0;
        }

        header {
            position: fixed;
            top: -80px;
            left: 0px;
            right: 0px;
            height: 50px;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            line-height: 35px;
        }

        footer {
            position: fixed;
            bottom: -40px;
            left: 0px;
            right: 0px;
            height: 30px;
            text-align: center;
            font-size: 12px;
            line-height: 20px;
        }

        .content {
            text-align: left;
            font-size: 14px;
        }

    </style>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">


    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    {{-- <title>{{ $columns[0]['title'] ?? 'Report' }}</title> --}}
</head>

<body>

    <div class="margin-top">
        <table class="table table-bordered">
            <thead>
                <tr>
                    @foreach($genericData['headers'] as $col)
                    <th>{{ $col['header'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($genericData['data'] as $row)
                <tr>
                    @foreach($genericData['headers'] as $col) {{-- ✅ FIX HERE --}}
                    {{-- <td>{{ $row[$col['key']] ?? '' }}</td> --}}
                    <td>{{ $row->{$col['key']} ?? '' }}</td>
                    @endforeach
                </tr>
                @empty
                <tr>
                    <td colspan="{{ count($genericData['headers']) }}" class="text-center">No data available</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
