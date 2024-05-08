## Step Running The Telegram Blasting System

The Telegram Blasting System using Laravel and Library (irazasyed/telegram-bot-sd)

- Run the command on the terminal
```bash
$ php artisan serve
```

## Connect To Telegram Bot
- Make sure the telegram user has a username (ex : @nen_28)
- Add bot (@swift28_bot) and click start to start blasting message. Like the picture below
![Screenshot_1](https://github.com/nuraeni28/telegram-blasting/assets/68740508/efcd2f41-4121-4b86-b015-f51f062b07cb)

- 

## Send Blasting With API 

```http
GET /api/blast-message
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
  "data"    : string
}
```


