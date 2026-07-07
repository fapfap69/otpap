// ASP.NET Core Hello World API protected by OTPAP.
using System.Text.Json;
using OtpapHelloWorld;

var builder = WebApplication.CreateBuilder(args);
var app = builder.Build();
var secret = "csharp-hello-world-secret";
var replayStore = new HashSet<string>();
var session = new Dictionary<string, object?>
{
    ["applicationId"] = "hello-world-app",
    ["sessionId"] = "sess_csharp_001",
    ["userId"] = "user_hello",
    ["pageId"] = "page_hello",
    ["apiId"] = "hello.world",
    ["httpMethod"] = "POST",
    ["sequenceNumber"] = 1
};

app.MapGet("/page", () =>
{
    // The page receives a token bound to the Hello World API request.
    var token = Otpap.CreateToken(new Dictionary<string, object?>(session)
    {
        ["body"] = "{\"message\":\"Hello World\"}"
    }, secret);
    return Results.Json(new { page = "Hello World", token });
});

app.MapPost("/api/hello", async context =>
{
    // Validate the token before executing the Hello World handler.
    var tokenJson = context.Request.Headers["X-OTPAP-Token"].ToString();
    if (string.IsNullOrWhiteSpace(tokenJson))
    {
        return Results.Json(new { valid = false, code = "OTPAP-1001", message = "Missing token." }, statusCode: StatusCodes.Status400BadRequest);
    }

    var rawToken = JsonSerializer.Deserialize<Dictionary<string, JsonElement>>(tokenJson)!;
    var token = rawToken.ToDictionary(
        pair => pair.Key,
        pair => pair.Value.ValueKind switch
        {
            JsonValueKind.String => pair.Value.GetString(),
            JsonValueKind.Number when pair.Value.TryGetInt64(out var number) => number,
            JsonValueKind.True => true,
            JsonValueKind.False => false,
            _ => pair.Value.ToString()
        }
    );
    using var reader = new StreamReader(context.Request.Body);
    var body = await reader.ReadToEndAsync();
    var result = Otpap.ValidateToken(
        token,
        new Dictionary<string, object?>(session)
        {
            ["body"] = body
        },
        secret,
        replayStore);
    if (result.TryGetValue("valid", out var valid) && valid is bool isValid && !isValid)
    {
        return Results.Json(result, statusCode: StatusCodes.Status409Conflict);
    }

    return Results.Json(new { valid = true, code = "OTPAP-0000", message = "Hello World", consumed = true });
});

app.Run("http://localhost:5000");
