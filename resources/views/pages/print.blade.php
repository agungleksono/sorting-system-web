<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <b>TEST PRINT</b>
    <h3>SUSPECT</h3>
    <p>Lorem ipsum dolor sit, amet consectetur adipisicing elit. Fugit totam laboriosam iure et fuga cupiditate dolor libero vero? Rerum sapiente assumenda voluptas perferendis possimus id hic architecto et facilis sunt.</p>
</body>
</html>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Your code here
        console.log("Document is ready!");
        checkPrintQueue();
    });

    function checkPrintQueue() {
        let token = 'tt793cqBV3JffiVbm4sVOJ33ghcXB5IrWiFTo4oRsgIq9LPZpUU31bpBLjWjECGj';

        fetch('http://127.0.0.1:8000/api/v1/print-queue', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`, // Add the Bearer token here
                'Content-Type': 'application/json', // Optional: Set the content type
            },
        })
        .then(response => response.json())
        .then(data => {
            // console.log(data.data.printQueue)
            if (data.data.printQueue) {
                // Call your print method if there is a print queue
                // console.log('Printing: ', data.queue);
                // printJob(data.queue);
                window.print();
            }
        })
        .catch(error => console.error('Error checking print queue:', error));
    }

    // setInterval(checkPrintQueue, 2000);
</script>