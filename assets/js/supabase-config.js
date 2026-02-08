// Supabase Configuration
const SUPABASE_URL = 'https://ogfbxnamadhdpwjyhsjn.supabase.co';
const SUPABASE_ANON_KEY = 'sb_publishable_WU9m-JlGCvHSxlYHXam-Iw_J4oZIzvc';

// The Supabase Client Instance (renamed to avoid conflict with the library)
let supabaseClient;

function initSupabase() {
    // The CDN library provides a global 'supabase' object with 'createClient'
    if (typeof supabase !== 'undefined' && typeof supabase.createClient === 'function') {
        supabaseClient = supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY);
        console.log('Supabase Client Initialized');
    } else {
        console.error('Supabase library not found. Make sure the CDN script is loaded.');
    }
}

async function sendNotification(data) {
    if (!supabaseClient) {
        console.error('Supabase client not initialized');
        return { error: 'Client not initialized' };
    }

    const { data: funcData, error } = await supabaseClient.functions.invoke('send-notification', {
        body: data,
    });

    if (error) console.error('Error sending notification:', error);
    return { data: funcData, error };
}
