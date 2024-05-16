## Step Running The Telegram Blasting System

The Telegram Blasting System using Laravel and Library (irazasyed/telegram-bot-sd)

- Run the command on the terminal
```bash
$ php artisan migrate
```
```bash
$ php artisan serve
```

- Run the command on the other terminal
```bash
$ php artisan queue:work
```

## Connect To Telegram Bot
- Make sure the telegram user has a username (ex : @nen_28)
- Add bot (@swift28_bot) and click start to start blasting message. Like the picture below
![Uploading Screenshot_2.png…]


## Send Blasting With API 

```http
POST /api/blast-message
```
- Body
Example :
```javascript
[
    {
        "message": "example message with low priority",
        "usernames": ["nen_28"],
        "priority": "low"
    },
    {
        "message": "example message with low priority",
        "usernames": ["taufik27"],
        "priority": "high"
    }
]
```

- Responses
```javascript
{
  "message" : string,
  "success" : bool,
  "data"    : array
}
```


