'use client';

import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
  Label,
  Textarea,
} from '@/components/provider/ui';
import type { BasicInfoFormData } from '@/types/basic-info';

interface ObservacionesCardProps {
  formData: BasicInfoFormData;
  onFieldChange: (field: string, value: string | number | null) => void;
}

/**
 * Card: Observaciones
 * Single optional Textarea for additional notes
 *
 * Path: observations
 */
export function ObservacionesCard({
  formData,
  onFieldChange,
}: ObservacionesCardProps) {
  return (
    <Card className="mb-8">
      <CardHeader>
        <CardTitle>Observaciones</CardTitle>
        <CardDescription>
          Agrega cualquier información adicional o aclaración que consideres necesaria
        </CardDescription>
      </CardHeader>
      <CardContent className="space-y-2">
        <Label htmlFor="observations" className="text-sm font-medium text-[#1A1A1A]">
          Observaciones (Opcional)
        </Label>
        <Textarea
          id="observations"
          placeholder="Escribe aquí cualquier aclaración o información adicional..."
          value={formData.observations}
          onChange={(e) => onFieldChange('observations', e.target.value)}
          className="border-[#E0E0E0] bg-white rounded-[14px] resize-none"
        />
      </CardContent>
    </Card>
  );
}
