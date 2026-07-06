# ASP.NET Core Example

```csharp
using Microsoft.AspNetCore.Builder;
using Microsoft.AspNetCore.Http;

var builder = WebApplication.CreateBuilder(args);
var app = builder.Build();
var replayStore = new HashSet<string>();

app.MapPost("/api/orders/create", async context =>
{
    // Read the OTPAP token from a header or request envelope.
    var tokenJson = context.Request.Headers["X-OTPAP-Token"].ToString();
    var tokenId = "derived-token-id";

    // Replay detection is mandatory.
    if (!replayStore.Add(tokenId))
    {
        context.Response.StatusCode = StatusCodes.Status409Conflict;
        await context.Response.WriteAsJsonAsync(new { valid = false, code = "OTPAP-1010" });
        return;
    }

    await context.Response.WriteAsJsonAsync(new { valid = true, code = "OTPAP-0000", consumed = true });
});

app.Run();
```
