# Redactus

## Creating a team

```bash
curl -i -X POST 'https://redactus.reidel.eu/team'
```

## Joining a team

Websocket to `https://redactus.reidel.eu/team/<team>`

## Sending a guess

```json
{"type": "guess", "word": "Test", "number": 1, "sender": "Incognito"}
```
