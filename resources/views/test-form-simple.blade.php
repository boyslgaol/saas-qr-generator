<!DOCTYPE html>
<html>
<head>
    <title>Test Form</title>
</head>
<body>
    <h1>Test Form Submit</h1>
    
    <form action="/test-post" method="POST">
        @csrf
        <input type="text" name="name" placeholder="Nama" class="border p-2">
        <button type="submit">Submit</button>
    </form>
</body>
</html>