# Redactus

## Creating a team

```bash
curl -H 'Content-type: application/json' -d '{"type": "riddle-nr", "nr": 177}' -X POST 'https://redactus.reidel.eu/team'
```

Reply:

```json
{"team-id":"9nLHoBhqTJ9","nr":177}
```

## Joining a team

Websocket to `https://redactus.reidel.eu/team/<team>`

Welcome:

```json
{"type":"riddle-nr","nr":177}
{"type":"team-size","size":1}
```

## Sending a guess

```json
{"type": "guess", "word": "Test", "sender": "Incognito"}
```
