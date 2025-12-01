<?php include '../backend/auth_guard.php'; ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบบันทึกค่าไฟฟ้าและน้ำประปาสำหรับบ้านพักครูในวิทยาลัย</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        body { 
            font-family: 'Kanit', sans-serif; 
            background-color: #f8f9fc;
            overflow-x: hidden; /* ป้องกัน Scroll แนวนอน */
        }

        #wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
            transition: all 0.25s ease-out;
        }

        /* Sidebar Styles */
        #sidebar-wrapper { 
            min-width: 260px; 
            max-width: 260px; 
            background-color: #2c3e50; 
            color: white; 
            transition: margin 0.25s ease-out;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1); /* เพิ่มเงาให้ Sidebar ดูลอยเด่น */
            z-index: 1000;
        }
        
        #sidebar-wrapper .sidebar-heading { 
            padding: 1.5rem 1rem; 
            font-size: 1.2rem; 
            font-weight: 600;
            text-align: center; 
            background: #1a252f; 
            border-bottom: 1px solid #34495e;
        }
        
        /* Page Content */
        #page-content-wrapper { 
            flex: 1; 
            width: 100%; 
            display: flex;
            flex-direction: column; /* จัด Layout แนวตั้งเพื่อให้ Footer อยู่ล่างสุด */
        }

        /* === Logic การซ่อน/แสดง Sidebar (Desktop vs Mobile) === */
        
        /* Desktop: ปกติแสดง, Toggled คือซ่อน */
        #wrapper.toggled #sidebar-wrapper {
            margin-left: -260px; 
        }

        /* Mobile: ปกติซ่อน (-260px), Toggled คือแสดง (0) */
        @media (max-width: 768px) {
            #sidebar-wrapper {
                margin-left: -260px;
            }
            #wrapper.toggled #sidebar-wrapper {
                margin-left: 0;
            }
        }
        /* =================================================== */

        .list-group-item { 
            background-color: #2c3e50; 
            color: #bdc3c7; 
            border: none; 
            padding: 15px 25px;
            border-left: 4px solid transparent; /* เตรียมเส้นขอบไว้สำหรับ Active */
        }
        
        .list-group-item:hover { 
            background-color: #34495e; 
            color: #f1c40f; 
            text-decoration: none;
        }
        
        .list-group-item.active {
            background-color: #34495e;
            color: #fff;
            font-weight: 500;
            border-left-color: #f1c40f; /* เส้นสีเหลืองด้านซ้าย */
        }
        
        .navbar-custom {
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,.05);
            padding: 0.8rem 1rem;
        }
    </style>
</head>
<body>
    <div id="wrapper">