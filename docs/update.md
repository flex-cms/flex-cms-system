# Актуализиране на версията

- Взимане на всички файлове и директории в ядрото на системата, с изключение на:
    - node_modules
    - vendor
    - storage
    - themes
    - plugins
    - uploads

    Трябва да се създаде update.zip архив файл, в който по-горе посочените директории трябва да са изключени от архива. Файлът трябва да бъде в директорията на **updates.kriskata.com/versions/номера-на-версията/update.zip**.

- С помоща на Bash Terminal в Windows трябва да се генерира ключ, който ще бъде предоставен във файла. При всяка промяна вернията трябва да се обнови този файл по този начин:

**updates.kriskata.com/updates/latest.json**

    {
        "latest_version": "0.0.4",
        "release_date": "2026-06-14",
        "min_php_version": "8.2",
        "download_url": "https://updates.kriskata.com/versions/0.0.4/update.zip",
        "checksum": "sha256:281b4d5087ca6deb58206246f585348999898c00d52e97236ffc0a662ba40f90",
        "requires_migration": true,
        "changelog": [
            "Подобрена сигурност при зареждане на файлове",
            "Оптимизация на заявките към базата данни",
            "Добавени нови системни миграции"
        ]
    }
