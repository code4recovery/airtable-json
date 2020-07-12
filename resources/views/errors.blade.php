<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css" integrity="sha384-r4NyP46KrjDleawBgD5tp8Y7UzmLA05oM1iAEQ17CSuDqnUK2+k9luXQOfXJCJ4I" crossorigin="anonymous">
    <title>Airtable Import Errors</title>
  </head>
  <body>
    <div class="container mt-3">
        <h1>Airtable Import Errors</h1>
        @if (count($errors))
        <ol>
        @foreach ($errors as $error)
            <li>
                <a href="https://airtable.com/tblZxISFYVgvtHmd9/viwVDmtNXnNvrtATs/{{ $error['id'] }}" target="_blank">
                    {{ $error['name'] ?? 'No meeting name' }}</a>

                {{ $error['issue'] }}
                <code>{{ @$error['value'] }}</code>
            </li>
        @endforeach
        </ol>
        @else
        <div class="alert alert-success">
            All good
        </div>
        @endif
    </div>
  </body>
</html>