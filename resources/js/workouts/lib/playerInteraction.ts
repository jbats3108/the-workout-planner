import { prepareRestAlerts } from '@/workouts/lib/restAlert';
import { primeScreenWake } from '@/workouts/lib/screenWake';

/** Prime browser features that need a recent user gesture. */
export function preparePlayerInteraction(): void {
    prepareRestAlerts();
    primeScreenWake();
}
