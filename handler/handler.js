$(document).ready(function () {
    const pageUrl = window.location.href;
    console.log('Page URL:', pageUrl);
    const parsedData = parseEdxUrl(pageUrl);
    console.log('Parsed Data:', parsedData);

    if (parsedData) {
        $.ajax({
            type: 'POST',
            url: 'https://your-server-address/NewWorldMap/handler/map_handler.php', // URL to your PHP backend script
            data: parsedData,
            dataType: 'json',
            success: function (response) {
                console.log('AJAX Success Response:', response);
                if (response.success) {
                    const iframeHtml = `<iframe style="border: none;" src="${response.mapUrl}" height="615" width="100%"></iframe>`;
                    $('#map-container').html(iframeHtml);
                } else {
                    $('#map-container').html('<p>Error loading map: ' + response.message + '</p>');
                }
            },
            error: function (error) {
                console.log('AJAX Error:', error);
                $('#map-container').html('<p>Error loading map. Please try again later.</p>');
            }
        });
    } else {
        $('#map-container').html('<p>Invalid URL format. Unable to parse course details.</p>');
    }
});

function parseEdxUrl(url) {
    const sanitizedUrl = url.replace(/\/\//g, '/');
    console.log('Sanitized URL:', sanitizedUrl);
    const urlParts = sanitizedUrl.split('/');
    let course_code = '';
    let course_run = '';
    let map_id = '';

    if (url.includes('studio.edx.org')) {
        const blockId = urlParts[urlParts.length - 1];
        [course_code, course_run] = blockId.split('+').slice(1, 3);
        map_id = blockId.split('@')[1];
        console.log('Parsed Studio URL:', { course_code, course_run, map_id });

    } else if (url.includes('learning.edx.org')) {
        const coursePart = urlParts[4].split(':')[1];
        const courseParts = coursePart.split('+');
        course_code = courseParts[1];
        course_run = courseParts[2];
        map_id = urlParts[urlParts.length - 1].split('@')[1];
        console.log('Parsed Learning URL:', { course_code, course_run, map_id });

    } else if (url.includes('course-authoring.edx.org')) {
        const coursePart = urlParts[4].split(':')[1];
        const courseParts = coursePart.split('+');
        course_code = courseParts[1];
        course_run = courseParts[2];
        map_id = urlParts[urlParts.length - 1].split('@')[1];
        console.log('Parsed Course Authoring URL:', { course_code, course_run, map_id });
    }

    if (course_code && course_run && map_id) {
        return {
            course_code: course_code,
            course_run: course_run,
            map_id: map_id
        };
    }

    return null;
}