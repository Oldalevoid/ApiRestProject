
README
Applicazione PHP con API RESTful per la gestione di materie e corsi
Questo progetto è un'applicazione PHP che offre API RESTful per la gestione di materie e corsi. L'applicazione utilizza un database MySQL per memorizzare le informazioni.

Requisiti
PHP 7.0 o versioni successive
MySQL
Un server web (ad esempio, Apache)

Installazione
1) Clona il repository su una directory locale:
- git clone https://github.com/tuonome/progetto-php-mysql.git

2) Crea il database MySQL e importa lo schema dal file database.sql.
- mysql -u nome_utente -p nome_database < database.sql

3) Configura le credenziali del database nel file config.php.

Utilizzo delle API
L'applicazione offre le seguenti API per la gestione di materie e corsi:

Materie (subjects)

A) Aggiungi una materia (POST)


- curl -X POST -H "Content-Type: application/json" -d '{"subject_id": 1, "subject_name": "Matematica"}' http://localhost/materie.php


B) Modifica una materia (PUT)

- curl -X PUT -H "Content-Type: application/json" -d '{"subject_id": 1, "subject_name": "Chimica"}' http://localhost/materie.php

C) Elimina una materia (DELETE)

- curl -X DELETE -H "Content-Type: application/json" -d '{"subject_id": 2}' http://localhost/materie.php

Corsi (courses)

A) Aggiungi un corso (POST)


- curl -X POST -H "Content-Type: application/json" -d '{"course_id": 1, "course_name": "Informatica", "available_seats": 20}' http://localhost/courses.php

B) Modifica un corso (PUT)

- curl -X PUT -H "Content-Type: application/json" -d '{"course_id": 1, "course_name": "Biologia", "available_seats": 15}' http://localhost/courses.php

C) Elimina un corso (DELETE)

 - curl -X DELETE -H "Content-Type: application/json" -d '{"course_id": 2}' http://localhost/courses.php



Filtra i corsi


- curl -X GET -H "Content-Type: application/json" "http://localhost/api/courses.php?course_name=Informatica&subject_id=1&available_seats=20"

Test

- Per testare l'applicazione, puoi utilizzare strumenti come cURL o Postman per effettuare richieste alle API e verificare le risposte.






L'applicazione offre le seguenti API per la gestione di materie e corsi:
