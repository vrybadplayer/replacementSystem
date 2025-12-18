<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - Something Went Wrong</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #ff9a9e 0%, #fad0c4 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
        }
        
        .error-container {
            text-align: center;
            padding: 40px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            max-width: 600px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
        }
        
        .error-icon {
            font-size: 80px;
            margin-bottom: 20px;
            color: #ff6b6b;
        }
        
        .error-code {
            font-size: 100px;
            font-weight: 800;
            margin: 0;
            line-height: 1;
            color: #ff6b6b;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .error-title {
            font-size: 32px;
            font-weight: 600;
            margin: 20px 0 10px;
            color: #333;
        }
        
        .error-message {
            font-size: 18px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .error-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }
        
        .btn {
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            font-size: 16px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        
        .btn-primary {
            background: #ff6b6b;
            color: white;
        }
        
        .btn-primary:hover {
            background: #ff5252;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        
        .error-reference {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            border-left: 4px solid #ff6b6b;
        }
        
        .reference-title {
            font-size: 14px;
            color: #666;
            margin: 0 0 5px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .reference-code {
            font-family: 'Courier New', monospace;
            font-size: 16px;
            color: #333;
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .error-code {
                font-size: 80px;
            }
            
            .error-title {
                font-size: 24px;
            }
            
            .error-message {
                font-size: 16px;
            }
            
            .error-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">⚠️</div>
        <h1 class="error-code">{{ $statusCode ?? '500' }}</h1>
        <h2 class="error-title">{{ $title ?? 'Something Went Wrong' }}</h2>
        <p class="error-message">
            {{ $message ?? 'An unexpected error occurred while processing your request. Our team has been notified and is working to resolve the issue.' }}
        </p>
        
        <div class="error-actions">
            <a href="javascript:history.back()" class="btn btn-secondary">Go Back</a>
            <a href="{{ url('/') }}" class="btn btn-primary">Go Home</a>
        </div>
        
        <div class="error-reference">
            <p class="reference-title">Error Reference</p>
            <p class="reference-code">ERR-{{ $statusCode ?? '500' }}-{{ date('YmdHis') }}</p>
        </div>
    </div>
</body>
</html>