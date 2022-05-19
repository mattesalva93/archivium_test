<!DOCTYPE html>
<html lang="en">

<head>
    <title>Test Archivium</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
        #frm-create-post label.error {
            color: red;
        }
    </style>
</head>
<body>
        <div class="container" style="margin-top: 50px;">
            <h4 style="text-align: center;">Test Archivium</h4>
            {{-- Messaggio di successo per il caricamento dei file nel database --}}
            @if (session()->has('message'))
            <div class="alert alert-success alert-block">
                <button type="button" class="close" data-dismiss="alert">×</button>
                {{ session()->get('message') }}
            </div>
            @endif
            {{-- Form per inserire file .zip contententi fatture .xml che verranno aperti estratti e pushati nel db --}}
            <form action="{{ route('xml-unzip') }}" id="frm-create-course" method="post"
                enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="file">Aggiungi un file zip:</label>
                    <input type="file" class="form-control" required id="file" name="file">
                </div>
                <button type="submit" class="btn btn-primary" id="submit-post">Invia</button>
            </form>
        </div>
</body>

</html>
