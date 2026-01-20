import { format, getMinutes } from 'date-fns';

export function formatEventDateTime(dateString: string): string {
    const date = new Date(dateString);
    const minutes = getMinutes(date);
    const timeFormat = minutes === 0 ? 'haaa' : 'h:mmaaa';
    return format(date, `MMMM do 'at' ${timeFormat}`);
}

export function useDateFormat() {
    return { formatEventDateTime };
}
