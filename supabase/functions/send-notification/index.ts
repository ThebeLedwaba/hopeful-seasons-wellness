import { serve } from "https://deno.land/std@0.168.0/http/server.ts"

const RESEND_API_KEY = Deno.env.get('RESEND_API_KEY')

const corsHeaders = {
    'Access-Control-Allow-Origin': '*',
    'Access-Control-Allow-Headers': 'authorization, x-client-info, apikey, content-type',
}

serve(async (req) => {
    // Handle CORS preflight requests
    if (req.method === 'OPTIONS') {
        return new Response('ok', { headers: corsHeaders })
    }

    console.log('Function triggered');

    if (!RESEND_API_KEY) {
        console.error('RESEND_API_KEY is not set in Supabase secrets');
        return new Response(JSON.stringify({ error: 'Server configuration error: Missing API Key' }), {
            status: 500,
            headers: { ...corsHeaders, 'Content-Type': 'application/json' }
        });
    }

    try {
        const payload = await req.json();
        console.log('Payload received:', JSON.stringify(payload, null, 2));

        const { type, name, email, phone, service_type, preferred_date, message, client_name } = payload;

        let subject = ""
        let html = ""

        if (type === 'booking') {
            subject = `New Booking Request: ${name}`
            html = `
        <h2>New Booking Request</h2>
        <p><strong>Name:</strong> ${name || 'N/A'}</p>
        <p><strong>Email:</strong> ${email || 'N/A'}</p>
        <p><strong>Phone:</strong> ${phone || 'N/A'}</p>
        <p><strong>Service:</strong> ${service_type || 'N/A'}</p>
        <p><strong>Preferred Date:</strong> ${preferred_date || 'N/A'}</p>
      `
        } else if (type === 'contact') {
            subject = `New Contact Message: ${name}`
            html = `
        <h2>New Message from Website</h2>
        <p><strong>Name:</strong> ${name || 'N/A'}</p>
        <p><strong>Email:</strong> ${email || 'N/A'}</p>
        <p><strong>Phone:</strong> ${phone || 'N/A'}</p>
        <p><strong>Message:</strong></p>
        <p>${message || 'N/A'}</p>
      `
        } else if (type === 'intake') {
            subject = `New Intake Form Submitted: ${client_name}`
            html = `
        <h2>Diagnostic Intake Form Submitted</h2>
        <p><strong>Client:</strong> ${client_name || 'N/A'}</p>
        <p>A new clinical assessment has been submitted and is ready for review in your Supabase dashboard.</p>
      `
        } else {
            // Fallback for debugging or unknown types
            subject = `Notification System Test: ${type || 'Unknown Type'}`
            html = `
                <h2>Notification Received</h2>
                <p><strong>Raw Payload:</strong></p>
                <pre>${JSON.stringify(payload, null, 2)}</pre>
            `
        }

        console.log(`Sending email: ${subject} to thebeledwaba@gmail.com`);

        const res = await fetch('https://api.resend.com/emails', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${RESEND_API_KEY}`,
            },
            body: JSON.stringify({
                from: 'Hopeful Seasons <onboarding@resend.dev>',
                to: ['info@hopefulseasonswellness.co.za'],
                subject: subject,
                html: html,
            }),
        });

        const data = await res.json();
        console.log('Resend response:', JSON.stringify(data, null, 2));

        if (!res.ok) {
            console.error('Resend API error:', data);
            return new Response(JSON.stringify({ error: 'Resend API error', details: data }), {
                status: res.status,
                headers: { ...corsHeaders, 'Content-Type': 'application/json' }
            });
        }

        return new Response(JSON.stringify(data), {
            status: 200,
            headers: { ...corsHeaders, 'Content-Type': 'application/json' }
        });
    } catch (error) {
        console.error('Internal Function Error:', error);
        return new Response(JSON.stringify({ error: error.message }), {
            status: 500,
            headers: { ...corsHeaders, 'Content-Type': 'application/json' }
        });
    }
});
