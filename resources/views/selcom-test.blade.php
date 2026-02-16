<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Selcom Payment Test</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }

        p {
            color: #666;
            margin-bottom: 30px;
        }

        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            width: 100%;
            margin-bottom: 15px;
        }

        button:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }

        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .hidden {
            display: none;
        }

        .response-box {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }

        .success {
            background: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }

        .error {
            background: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }

        .response-details {
            margin-top: 15px;
        }

        .response-details div {
            margin: 8px 0;
            padding: 8px;
            background: white;
            border-radius: 5px;
            font-size: 14px;
        }

        .response-details strong {
            color: #333;
        }

        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
            display: inline-block;
            margin-left: 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .btn-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔒 Selcom Payment Test</h1>
        <p>Test the Selcom payment gateway integration</p>

        <button id="testBtn" class="btn-primary" onclick="initiatePayment()">
            Initiate Payment Test
        </button>

        <div id="responseBox" class="response-box hidden"></div>

        <button id="redirectBtn" class="btn-success hidden" onclick="redirectToPayment()">
            Proceed to Payment Gateway →
        </button>
    </div>

    <script>
        let paymentUrl = null;
        let responseData = null;

        async function initiatePayment() {
            const testBtn = document.getElementById('testBtn');
            const responseBox = document.getElementById('responseBox');
            const redirectBtn = document.getElementById('redirectBtn');

            // Disable button and show loading state
            testBtn.disabled = true;
            testBtn.innerHTML = 'Processing<span class="spinner"></span>';
            
            // Hide previous results
            responseBox.classList.add('hidden');
            redirectBtn.classList.add('hidden');
            paymentUrl = null;

            try {
                const response = await fetch('/api/selcom-test', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    }
                });

                const data = await response.json();
                responseData = data;

                // Show response box
                responseBox.classList.remove('hidden');

                if (response.ok && data.status === 'success') {
                    // Success response
                    responseBox.className = 'response-box success';
                    responseBox.innerHTML = `
                        <strong>✅ Payment Order Created Successfully!</strong>
                        <div class="response-details">
                            <div><strong>Status:</strong> ${data.status}</div>
                            <div><strong>Reference:</strong> ${data.reference || 'N/A'}</div>
                            <div><strong>Transaction ID:</strong> ${data.transid || 'N/A'}</div>
                            <div><strong>Payment URL:</strong> ${data.payment_url ? '✓ Ready' : '✗ Not available'}</div>
                        </div>
                    `;

                    if (data.payment_url) {
                        paymentUrl = data.payment_url;
                        redirectBtn.classList.remove('hidden');
                    }
                } else {
                    // Error response
                    responseBox.className = 'response-box error';
                    responseBox.innerHTML = `
                        <strong>❌ Error</strong>
                        <div class="response-details">
                            <div><strong>Status:</strong> ${data.status || 'error'}</div>
                            <div><strong>Message:</strong> ${data.message || 'Unknown error occurred'}</div>
                        </div>
                    `;
                }

                console.log('Full response:', data);

            } catch (error) {
                // Network or other errors
                responseBox.classList.remove('hidden');
                responseBox.className = 'response-box error';
                responseBox.innerHTML = `
                    <strong>❌ Request Failed</strong>
                    <div class="response-details">
                        <div><strong>Error:</strong> ${error.message}</div>
                    </div>
                `;
                console.error('Error:', error);
            } finally {
                // Re-enable button
                testBtn.disabled = false;
                testBtn.innerHTML = 'Initiate Payment Test';
            }
        }

        function redirectToPayment() {
            if (paymentUrl) {
                window.location.href = paymentUrl;
            } else {
                alert('Payment URL not available');
            }
        }
    </script>
</body>
</html>
