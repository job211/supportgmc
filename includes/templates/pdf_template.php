<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Cahier des Charges - {{project_name}}</title>
    <style>
        @page {
            margin: 80px 25px 50px 25px;
        }
        body {
            font-family: "Helvetica", sans-serif;
            color: #333;
            font-size: 11px;
            line-height: 1.5;
        }
        .header {
            position: fixed;
            top: -70px;
            left: 0;
            right: 0;
            height: 50px;
            border-bottom: 2px solid {{primaryColor}};
            padding: 0 10px;
        }
        .header .logo {
            float: left;
            height: 45px;
        }
        .header .header-text {
            float: right;
            text-align: right;
            color: {{primaryColor}};
            margin-top: 5px;
        }
        .footer {
            position: fixed;
            bottom: -40px;
            left: 0;
            right: 0;
            height: 30px;
            text-align: center;
            font-size: 9px;
            color: #777;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }
        .pagenum:before {
            content: counter(page);
        }
        .title-block {
            text-align: center;
            margin-bottom: 30px;
        }
        .title-block h1 {
            font-size: 26px;
            color: {{primaryColor}};
            margin: 0;
        }
        .title-block h2 {
            font-size: 20px;
            color: #555;
            margin: 5px 0 0 0;
            font-weight: normal;
        }
        .project-meta {
            border: 1px solid {{borderColor}};
            background-color: {{headerBgColor}};
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
            font-size: 12px;
        }
        .project-meta table {
            width: 100%;
            border-collapse: collapse;
        }
        .project-meta td {
            padding: 4px 8px;
            vertical-align: top;
        }
        .project-meta strong {
            color: {{primaryColor}};
        }
        .content-section table {
            width: 100%;
            page-break-inside: avoid;
            margin-bottom: 20px;
            border: 1px solid {{borderColor}};
            border-collapse: collapse;
        }
        .content-section tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        .content-section th {
            background-color: {{primaryColor}} !important;
            color: #ffffff !important;
            font-size: 16px;
            padding: 10px;
            text-align: left;
        }
        .content-section td {
             padding: 12px;
             border: 1px solid {{borderColor}};
             vertical-align: top;
        }
        .content-section td table {
            margin-top: 10px;
            border: 1px solid #ccc;
        }
        .content-section td table th {
            background-color: #f2f2f2 !important;
            color: #333 !important;
            font-size: 12px;
        }
        .content-section td table td {
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{logoSrc}}" alt="Logo" class="logo">
        <div class="header-text">
            <p style="font-size: 16px; font-weight: bold; margin: 0;">SUPPORT GMC</p>
            <p style="font-size: 12px; margin: 0;">Cahier des Charges Technique et Fonctionnel</p>
        </div>
    </div>
    <div class="footer">
        {{project_name}} - Page <span class="pagenum"></span>
    </div>
    <main>
        <div class="title-block">
            <h1>{{project_name}}</h1>
            <h2>Client : {{client_name}}</h2>
        </div>
        <div class="project-meta">
            <table>
                <tr>
                    <td width="50%"><strong>Demandeur :</strong> {{creator_username}}</td>
                    <td width="50%"><strong>Date de création :</strong> {{created_at}}</td>
                </tr>
                <tr>
                    <td><strong>Version :</strong> {{version}}</td>
                    <td><strong>Dernière mise à jour :</strong> {{updated_at}}</td>
                </tr>
                 <tr>
                    <td><strong>Statut :</strong> {{status}}</td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="2"><strong>Collaborateurs :</strong> {{stakeholders}}</td>
                </tr>
            </table>
        </div>
        <div class="content-section">
            {{content}}
        </div>
    </main>
</body>
</html>
